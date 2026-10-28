'use strict';
module.exports = {
  async history(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    ctx.body = { data: await strapi.service('api::compra.pedidos').history(ctx.state.user.id) };
  },
  async checkout(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    try {
      ctx.body = { data: await strapi.service('api::compra.pedidos').checkout(ctx.state.user, ctx.request.body || {}) };
    } catch (error) {
      strapi.log.error('Checkout rechazado: ' + error.message);
      const expected = /^(Elegí|Completá|El efectivo|El carrito|Este carrito|Tu carrito|Un producto|Cantidad inválida|Precio inválido|Stock insuficiente)/.test(error.message);
      ctx.badRequest(expected ? error.message : 'No se pudo guardar el pedido. Intentá de nuevo.');
    }
  }
};
