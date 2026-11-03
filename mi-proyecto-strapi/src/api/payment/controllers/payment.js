'use strict';
const { MercadoPagoConfig, Preference } = require('mercadopago');

module.exports = {
  async createPreference(ctx) {
    try {
      // Inicializar el cliente con el token de Render
      const client = new MercadoPagoConfig({ accessToken: process.env.MP_ACCESS_TOKEN });
      const preference = new Preference(client);

      // Los datos del carrito que enviarás desde tu frontend PHP
      const { items } = ctx.request.body;

      const response = await preference.create({
        body: {
          items: items, // Array de objetos: [{ id, title, quantity, unit_price }]
          back_urls: {
            success: 'https://tu-dominio-en-infinityfree.com/checkout/success',
            failure: 'https://tu-dominio-en-infinityfree.com/checkout/failure',
            pending: 'https://tu-dominio-en-infinityfree.com/checkout/pending'
          },
          auto_return: 'approved',
          // URL de tu Strapi en Render para recibir notificaciones en segundo plano
          // notification_url: 'https://tu-strapi-en-render.onrender.com/api/payments/webhook'
        }
      });

      // Devolver el link de pago al frontend
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