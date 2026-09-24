'use strict';

/**
 * envio service
 */

const { createCoreService } = require('@strapi/strapi').factories;

module.exports = createCoreService('api::envio.envio');
