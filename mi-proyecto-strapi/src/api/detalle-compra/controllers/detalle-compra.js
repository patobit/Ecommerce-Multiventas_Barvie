'use strict';
const {createCoreController} = require('@strapi/strapi').factories;
module.exports = createCoreController('api::detalle-compra.detalle-compra',({strapi}) => ({
  async find(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    ctx.query.filters = {$and:[ctx.query.filters || {},{compra:{users_permissions_user:{id:ctx.state.user.id}}}]};
    return super.find(ctx);
  },
  async findOne(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const own = await strapi.db.query('api::detalle-compra.detalle-compra').findOne({where:{documentId:ctx.params.id,compra:{users_permissions_user:{id:ctx.state.user.id}}}});
    if (!own) return ctx.notFound();
    return super.findOne(ctx);
  },
  async create(ctx) {return ctx.forbidden();},
  async update(ctx) {return ctx.forbidden();},
  async delete(ctx) {return ctx.forbidden();}
}));
