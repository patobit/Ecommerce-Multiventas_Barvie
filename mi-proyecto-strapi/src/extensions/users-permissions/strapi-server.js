'use strict';

// Rutas de cuenta propia: nunca se acepta un id ni permisos desde el cliente.
module.exports = (plugin) => {
  const updateUser = plugin.controllers.user.update;
  plugin.controllers.user.profile = async (ctx) => {
    if (!ctx.state.user) return ctx.unauthorized();
    if (ctx.method === 'GET') return plugin.controllers.user.me(ctx);
    const body = ctx.request.body || {};
    const data = {};
    for (const key of ['Nombre', 'Apellido', 'Telefono', 'email']) {
      if (typeof body[key] !== 'string' || body[key].length > 254) {
        return ctx.badRequest('Revisá los datos personales.');
      }
      data[key] = body[key].trim();
    }
    if (!data.Nombre || !data.Apellido) return ctx.badRequest('Completá nombre y apellido.');
    data.email = data.email.toLowerCase();
    for (const [key, fields] of [
      ['Direcciones', ['provincia', 'ciudad', 'direccion']],
      ['Vehiculos', ['marca', 'modelo', 'anio']],
    ]) {
      if (!Array.isArray(body[key]) || body[key].length > 20) return ctx.badRequest('Máximo 20 direcciones o vehículos.');
      data[key] = [];
      for (const item of body[key]) {
        if (!item || typeof item !== 'object' || Array.isArray(item)) return ctx.badRequest('Datos inválidos.');
        const clean = {};
        for (const field of fields) {
          if (typeof item[field] !== 'string' || item[field].length > 254) return ctx.badRequest('Revisá las direcciones y vehículos.');
          clean[field] = item[field].trim();
        }
        if (key === 'Vehiculos' && clean.anio && !/^(18|19|20|21)\d{2}$/.test(clean.anio)) return ctx.badRequest('Revisá el año del vehículo.');
        if (Object.values(clean).some(Boolean)) data[key].push(clean);
      }
    }
    // Mantener compatibles los campos simples utilizados por el registro.
    const address = data.Direcciones[0] || {};
    const car = data.Vehiculos[0] || {};
    Object.assign(data, {
      Provincia: address.provincia || '', Ciudad: address.ciudad || '', Direccion: address.direccion || '',
      Auto_marca: car.marca || '', Auto_modelo: car.modelo || '', Auto_anio: car.anio ? Number(car.anio) : null,
    });
    ctx.params = { id: ctx.state.user.id };
    ctx.request.body = data;
    // Reutiliza validación de email, unicidad y salida sin contraseñas de Strapi.
    return updateUser(ctx);
  };
  plugin.routes['content-api'].routes.unshift(...['GET', 'PUT'].map((method) => ({
    method, path: '/profile', handler: (ctx) => plugin.controllers.user.profile(ctx),
    config: { prefix: '', auth: { scope: [] } },
  })));
  return plugin;
};
