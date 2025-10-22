
export default {
  bootstrap: () => import('./main.server.mjs').then(m => m.default),
  inlineCriticalCss: true,
  baseHref: '/analytics-agent/',
  locale: undefined,
  routes: [
  {
    "renderMode": 2,
    "route": "/analytics-agent"
  }
],
  entryPointToBrowserMapping: undefined,
  assets: {
    'index.csr.html': {size: 455, hash: 'c7581a046c8563915360b9b7d6d8a29dc9fa82da1ddf4f4baa2c62efabe7464f', text: () => import('./assets-chunks/index_csr_html.mjs').then(m => m.default)},
    'index.server.html': {size: 968, hash: '8de8354a08e0800e25b05b83633b364b75f96c65bf5f5ab5e8d32720b3af9bc0', text: () => import('./assets-chunks/index_server_html.mjs').then(m => m.default)},
    'index.html': {size: 21593, hash: '045841c307edda0e6a964d387f65ac4488267437c0965e8398779861cf9b48f2', text: () => import('./assets-chunks/index_html.mjs').then(m => m.default)},
    'styles-5INURTSO.css': {size: 0, hash: 'menYUTfbRu8', text: () => import('./assets-chunks/styles-5INURTSO_css.mjs').then(m => m.default)}
  },
};
