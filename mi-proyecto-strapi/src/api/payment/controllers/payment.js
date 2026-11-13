'use strict';
const { MercadoPagoConfig, Preference } = require('mercadopago');

module.exports = {
  async createPreference(ctx) {
    try {
      const client = new MercadoPagoConfig({ accessToken: process.env.MP_ACCESS_TOKEN });
      const preference = new Preference(client);

      const { items } = ctx.request.body;

      const response = await preference.create({
        body: {
          items: items,
          back_urls: {
            success: 'https://mvb.page.gd/src/views/compra_finalizada.php',
            failure: 'https://mvb.page.gd/src/views/carrito.php',
            pending: 'https://mvb.page.gd/src/views/compra_finalizada.php'
          },
          auto_return: 'approved',
        }
      });

      ctx.send({
        id: response.id,
        init_point: response.init_point
      });
    } catch (error) {
      console.error(error);
      ctx.throw(500, 'Error al crear la preferencia de pago');
    }
  }
};