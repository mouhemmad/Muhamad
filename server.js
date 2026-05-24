const http = require('http');
const fs = require('fs');
const path = require('path');

const PORT = 5000;

const routes = {
  '/': '/index.html',
  '/login': '/login.html',
  '/register': '/register.html',
  '/dashboard': '/dashboard.html',
  '/menu': '/menu.html',
  '/admin': '/admin.html',
  '/subscribe': '/subscribe.html',
};

const mime = {
  '.html': 'text/html; charset=utf-8',
  '.css':  'text/css',
  '.js':   'application/javascript',
  '.json': 'application/json',
  '.png':  'image/png',
  '.jpg':  'image/jpeg',
  '.jpeg': 'image/jpeg',
  '.svg':  'image/svg+xml',
  '.woff2':'font/woff2',
  '.woff': 'font/woff',
  '.ico':  'image/x-icon',
};

http.createServer((req, res) => {
  let urlPath = req.url.split('?')[0];
  const filePath = routes[urlPath] || urlPath;
  const fullPath = path.join(__dirname, filePath);
  const ext = path.extname(fullPath).toLowerCase();
  const contentType = mime[ext] || 'application/octet-stream';

  fs.readFile(fullPath, (err, data) => {
    if (err) {
      res.writeHead(404, { 'Content-Type': 'text/plain' });
      res.end('404 Not Found');
      return;
    }
    res.writeHead(200, { 'Content-Type': contentType });
    res.end(data);
  });
}).listen(PORT, '0.0.0.0', () => {
  console.log('Server running on port ' + PORT);
});
