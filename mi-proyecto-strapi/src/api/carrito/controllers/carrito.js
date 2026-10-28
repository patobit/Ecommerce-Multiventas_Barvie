'use strict';
const {randomBytes} = require('node:crypto');
const {createCoreController} = require('@strapi/strapi').factories;
const UID = 'api::carrito.carrito';
const populate = {detalle_carritos:{populate:{producto:{populate:['categoria','Imagen']}}}};
const publicCart = cart => ({
  id:cart.id,documentId:cart.documentId,Estado:cart.Estado,
  detalle_carritos:(cart.detalle_carritos || []).map(line => ({
    id:line.id,documentId:line.documentId,Cantidad:line.Cantidad,
    producto:line.producto ? {
      id:line.producto.id,documentId:line.producto.documentId,
      Nombre:line.producto.Nombre,Descripcion:line.producto.Descripcion,Marca:line.producto.Marca,
      Stock:line.producto.Stock,Precio:line.producto.Precio,Precio_oferta:line.producto.Precio_oferta,
      categoria:line.producto.categoria ? {id:line.producto.categoria.id,Nombre:line.producto.categoria.Nombre} : null,
      Imagen:(line.producto.Imagen || []).map(image=>({url:image.url}))
    }:null
  }))
});
module.exports = createCoreController(UID,({strapi}) => ({
  async find(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const state = ctx.query.filters?.Estado?.$eq;
    const where = {users_permissions_user:{id:ctx.state.user.id}};
    if (['Activo','Convertido','Abandonado'].includes(state)) where.Estado = state;
    const carts = await strapi.db.query(UID).findMany({where,populate,orderBy:{id:'desc'},limit:100});
    ctx.body = {data:carts.map(publicCart)};
  },
  async findOne(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const cart = await strapi.db.query(UID).findOne({where:{documentId:ctx.params.id,users_permissions_user:{id:ctx.state.user.id}},populate});
    if (!cart) return ctx.notFound();
    ctx.body = {data:publicCart(cart)};
  },
  async create(ctx) {
    if (!ctx.state.user) return ctx.unauthorized();
    const cart = await strapi.db.query(UID).create({data:{
      documentId:randomBytes(12).toString('hex'),Estado:'Activo',users_permissions_user:ctx.state.user.id
    }});
    ctx.body = {data:publicCart(cart)};
  },
  async update(ctx) {return ctx.forbidden();},
  async delete(ctx) {return ctx.forbidden();}
}));
