'use strict';
const {createCoreController} = require('@strapi/strapi').factories;
module.exports = createCoreController('api::compra.compra',({strapi}) => ({
  async find(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    ctx.body = {data:await strapi.service('api::compra.pedidos').history(ctx.state.user.id)};
  },
  async findOne(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const own = await strapi.service('api::compra.pedidos').history(ctx.state.user.id);
    const order = own.find(p => p.documentId === ctx.params.id);
    if (!order) return ctx.notFound();
    ctx.body = {data:order};
  },
  async create(ctx) {return ctx.forbidden('Usá el checkout de tu carrito.');},
  async update(ctx) {return ctx.forbidden();},
  async delete(ctx) {return ctx.forbidden();}
}));
