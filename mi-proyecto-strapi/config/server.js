module.exports = ({ env }) => ({
  host: env('HOST', '0.0.0.0'),
  port: env.int('PORT', 1337),
  // Pedidos artificiales: conservar el inventario hasta habilitar ventas reales.
  ordersDeductStock: env.bool('ORDERS_DEDUCT_STOCK', false),
  app: {
    keys: env.array('APP_KEYS'),
  },
  webhooks: {
    populateRelations: env.bool('WEBHOOKS_POPULATE_RELATIONS', false),
  },
});
