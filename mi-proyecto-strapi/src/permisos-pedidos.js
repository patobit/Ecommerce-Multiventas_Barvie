'use strict';

// Las rutas nuevas sólo están habilitadas para clientes autenticados.
module.exports = async function permisosPedidos(strapi) {
  const role = await strapi.db.query('plugin::users-permissions.role').findOne({where:{type:'authenticated'}});
  if (!role) return;
  const permissions = strapi.db.query('plugin::users-permissions.permission');
  for (const action of [
    'api::compra.pedidos.history','api::compra.pedidos.checkout',
    ...['find','findOne','create'].map(action => 'api::carrito.carrito.'+action),
    ...['find','findOne','create','update','delete'].map(action => 'api::detalle-carrito.detalle-carrito.'+action),
    ...['find','findOne'].flatMap(action => [
      'api::producto.producto.'+action,'api::categoria.categoria.'+action,
      'api::compra.compra.'+action,'api::detalle-compra.detalle-compra.'+action,'api::envio.envio.'+action
    ])
  ]) {
    const existing = await permissions.findOne({where:{action,role:{id:role.id}}});
    if (!existing) await permissions.create({data:{action,role:role.id}});
  }
};
