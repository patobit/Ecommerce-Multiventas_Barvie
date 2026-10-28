'use strict';
module.exports = { routes: [
  { method:'GET', path:'/mis-pedidos', handler:'pedidos.history', config:{auth:{scope:[]}} },
  { method:'POST', path:'/mis-pedidos/checkout', handler:'pedidos.checkout', config:{auth:{scope:[]}} }
]};
