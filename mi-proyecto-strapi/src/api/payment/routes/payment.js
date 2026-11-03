module.exports = {
  routes: [
    {
      method: 'POST',
      path: '/payments/create-preference',
      handler: 'payment.createPreference',
      config: {
        auth: false, // Permitir acceso público para la creación (o usa un token desde PHP)
      },
    }
  ]
};