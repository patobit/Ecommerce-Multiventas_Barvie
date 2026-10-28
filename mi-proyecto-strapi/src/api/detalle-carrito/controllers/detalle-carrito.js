'use strict';
const {createCoreController} = require('@strapi/strapi').factories;
const UID = 'api::detalle-carrito.detalle-carrito';
module.exports = createCoreController(UID,({strapi}) => ({
  async find(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    ctx.query.filters = {$and:[ctx.query.filters || {},{carrito:{users_permissions_user:{id:ctx.state.user.id}}}]};
    return super.find(ctx);
  },
  async findOne(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const line = await this.ownLine(ctx);
    if (!line) return ctx.notFound();
    return super.findOne(ctx);
  },
  async ownLine(ctx) {
    return strapi.db.query(UID).findOne({where:{documentId:ctx.params.id,carrito:{Estado:'Activo',users_permissions_user:{id:ctx.state.user.id}}},populate:['producto']});
  },
  async create(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const data = ctx.request.body?.data || {};
    if (typeof data.carrito !== 'string' || typeof data.producto !== 'string') return ctx.badRequest();
    const cart = await strapi.db.query('api::carrito.carrito').findOne({where:{documentId:data.carrito,Estado:'Activo',users_permissions_user:{id:ctx.state.user.id}}});
    const product = await strapi.db.query('api::producto.producto').findOne({where:{documentId:data.producto,publishedAt:{$notNull:true}}});
    const quantity = Number(data.Cantidad);
    if (!cart || !product) return ctx.notFound();
    if (!Number.isSafeInteger(quantity) || quantity < 1 || quantity > 9999 || (strapi.config.get('server.ordersDeductStock', false) && quantity > product.Stock)) return ctx.badRequest('Revisá la cantidad y el stock.');
    const line = await strapi.db.query(UID).create({data:{documentId:require('node:crypto').randomBytes(12).toString('hex'),Cantidad:quantity,carrito:cart.id,producto:product.id}});
    return {data:{id:line.id,documentId:line.documentId,Cantidad:line.Cantidad}};
  },
  async update(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const line = await this.ownLine(ctx);
    if (!line) return ctx.notFound();
    const quantity = Number(ctx.request.body?.data?.Cantidad);
    if (!Number.isSafeInteger(quantity) || quantity < 1 || quantity > 9999 || (strapi.config.get('server.ordersDeductStock', false) && quantity > (line.producto?.Stock || 0))) return ctx.badRequest('Revisá la cantidad y el stock.');
    const updated = await strapi.db.query(UID).update({where:{id:line.id},data:{Cantidad:quantity}});
    return {data:{id:updated.id,documentId:updated.documentId,Cantidad:updated.Cantidad}};
  },
  async delete(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const line = await this.ownLine(ctx);
    if (!line) return ctx.notFound();
    await strapi.db.query(UID).delete({where:{id:line.id}});
    return {data:{id:line.id,documentId:line.documentId}};
  }
}));
