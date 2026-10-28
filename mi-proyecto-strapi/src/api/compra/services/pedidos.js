'use strict';

const { randomBytes } = require('node:crypto');
const newDocumentId = () => randomBytes(12).toString('hex');
const PURCHASE = 'api::compra.compra';
const CART = 'api::carrito.carrito';
const PRODUCT = 'api::producto.producto';
const clean = (value, max = 254) => typeof value === 'string' ? value.trim().slice(0, max) : '';
const money = value => Math.round(Number(value) * 100);

module.exports = ({ strapi }) => ({
  async history(userId) {
    const orders = await strapi.db.query(PURCHASE).findMany({
      where: { users_permissions_user: { id: userId } },
      orderBy: { Fecha: 'desc' },
      populate: { detalle_compras: { populate: { producto: { populate: ['Imagen'] } } } }
    });
    return orders.map(order => ({
      documentId: order.documentId, Fecha: order.Fecha, Total: order.Total,
      Estado: order.Estado || 'Sin registrar', Metodo_entrega: order.Metodo_entrega || 'Sin registrar',
      Metodo_pago: order.Metodo_pago || 'Sin registrar', Estado_pago: order.Estado_pago || 'Sin registrar',
      Moneda: order.Moneda || 'ARS', Costo_envio: order.Costo_envio, Seguimiento: order.Seguimiento,
      Datos_cliente: order.Datos_cliente || {}, Notas: order.Notas,
      Productos_comprados: order.Productos_comprados || (order.detalle_compras || []).map(line => ({
        nombre: line.producto?.Nombre || 'Producto no disponible',
        marca: line.producto?.Marca || '', cantidad: line.Cantidad,
        precio_unitario: line.Precio_unitario,
        subtotal: Number(line.Precio_unitario) * line.Cantidad,
        imagen: line.producto?.Imagen?.[0]?.url || null
      }))
    }));
  },

  async checkout(user, body) {
    if (!['Retiro','Envio'].includes(body.entrega)) throw new Error('Elegí retiro o envío.');
    if (!['A coordinar','Transferencia','Efectivo al retirar'].includes(body.pago)) throw new Error('Elegí un método de pago.');
    if (body.entrega === 'Envio' && body.pago === 'Efectivo al retirar') throw new Error('El efectivo al retirar requiere retiro.');
    const customer = {
      nombre: clean(body.nombre), telefono: clean(body.telefono, 40),
      email: user.email, direccion: body.entrega === 'Envio' ? clean(body.direccion) : '',
      ciudad: body.entrega === 'Envio' ? clean(body.ciudad) : '',
      provincia: body.entrega === 'Envio' ? clean(body.provincia) : '',
      codigo_postal: body.entrega === 'Envio' ? clean(body.codigo_postal, 20) : ''
    };
    if (!customer.nombre || !customer.telefono) throw new Error('Completá nombre y teléfono.');
    if (body.entrega === 'Envio' && (!customer.direccion || !customer.ciudad || !customer.provincia || !customer.codigo_postal)) {
      throw new Error('Completá la dirección de envío.');
    }
    const deductStock = strapi.config.get('server.ordersDeductStock', false);
    return strapi.db.transaction(async ({ trx }) => {
      const carts = strapi.db.query(CART);
      const cart = await carts.findOne({
        where: { documentId: clean(body.carrito), users_permissions_user: { id: user.id } },
        populate: { compra: true, detalle_carritos: { populate: { producto: { populate: ['Imagen'] } } } }
      });
      if (!cart) throw new Error('El carrito no pertenece a tu cuenta.');
      const cartMeta = strapi.db.metadata.get(CART);
      const locked = await trx(cartMeta.tableName).where('id', cart.id).forUpdate().first();
      const stateColumn = cartMeta.attributes.Estado.columnName;
      if (locked[stateColumn] !== 'Activo') {
        const previous = await carts.findOne({ where: { id: cart.id }, populate: ['compra'] });
        if (previous.compra) return { documentId: previous.compra.documentId };
        throw new Error('Este carrito ya está cerrado.');
      }
      if (!cart.detalle_carritos?.length) throw new Error('Tu carrito está vacío.');
      const items = [];
      let totalCents = 0;
      const productMeta = strapi.db.metadata.get(PRODUCT);
      const stockColumn = productMeta.attributes.Stock.columnName;
      // Orden fijo de bloqueo para evitar interbloqueos entre compras.
      const lines = [...cart.detalle_carritos].sort((a,b) => (a.producto?.id || 0) - (b.producto?.id || 0));
      for (const line of lines) {
        const product = line.producto;
        if (!product || !product.publishedAt) throw new Error('Un producto ya no está disponible.');
        const quantity = Number(line.Cantidad);
        if (!Number.isSafeInteger(quantity) || quantity < 1 || quantity > 9999) throw new Error('Cantidad inválida.');
        const lockedProduct = await trx(productMeta.tableName).where('id', product.id).forUpdate().first();
        const priceColumn = productMeta.attributes.Precio.columnName;
        const offerColumn = productMeta.attributes.Precio_oferta.columnName;
        const price = money(lockedProduct[priceColumn]);
        const offer = money(lockedProduct[offerColumn]);
        const unit = offer > 0 && offer < price ? offer : price;
        if (!Number.isSafeInteger(unit) || unit <= 0) throw new Error('Precio inválido.');
        if (deductStock) {
          if (Number(lockedProduct[stockColumn]) < quantity) throw new Error('Stock insuficiente: ' + product.Nombre);
          const newStock = Number(lockedProduct[stockColumn]) - quantity;
          await strapi.db.query(PRODUCT).updateMany({ where: { documentId: product.documentId }, data: { Stock: newStock } });
        }
        items.push({
          producto_document_id: product.documentId, nombre: product.Nombre,
          marca: product.Marca || '', descripcion: product.Descripcion || '',
          cantidad: quantity, precio_unitario: unit / 100, subtotal: quantity * unit / 100,
          imagen: product.Imagen?.[0]?.url || null
        });
        totalCents += quantity * unit;
      }
      const order = await strapi.db.query(PURCHASE).create({ data: {
        documentId: newDocumentId(), Fecha: new Date().toISOString(), Total: totalCents / 100, Moneda: 'ARS',
        Estado: 'Pendiente', Estado_pago: 'Pendiente', Metodo_entrega: body.entrega,
        Metodo_pago: body.pago, Datos_cliente: customer, Productos_comprados: items,
        Costo_envio: body.entrega === 'Retiro' ? 0 : null,
        Notas: clean(body.notas, 1000), users_permissions_user: user.id
      }});
      for (let index = 0; index < lines.length; index++) {
        await strapi.db.query('api::detalle-compra.detalle-compra').create({ data: {
          documentId: newDocumentId(), compra: order.id, producto: lines[index].producto.id,
          Cantidad: items[index].cantidad, Precio_unitario: items[index].precio_unitario
        }});
      }
      if (body.entrega === 'Envio') {
        await strapi.db.query('api::envio.envio').create({data:{
          documentId:newDocumentId(),Estado:'Pendiente',compra:order.id,
          Direccion_entrega:[customer.direccion,customer.ciudad,customer.provincia,customer.codigo_postal].join(', ')
        }});
      }
      await carts.update({where:{id:cart.id},data:{Estado:'Convertido',compra:order.id}});
      return { documentId: order.documentId };
    });
  }
});
