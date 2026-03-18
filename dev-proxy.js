// dev-proxy.js
const express = require('express');
const { createProxyMiddleware } = require('http-proxy-middleware');

const app = express();

// Add CORS headers to all responses
app.use((req, res, next) => {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    res.header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');

    // Handle preflight requests
    if (req.method === 'OPTIONS') {
        res.sendStatus(200);
    } else {
        next();
    }
});

// Proxy API to PHP server
app.use('/api', createProxyMiddleware({
    target: 'http://localhost:8000/api',
    changeOrigin: true,
    logLevel: 'debug',
    onProxyReq: (proxyReq, req, res) => {
        console.log('Proxying:', req.method, req.url, '→', proxyReq.path);
    },
    onProxyRes: (proxyRes, req, res) => {
        proxyRes.headers['Access-Control-Allow-Origin'] = '*';
        proxyRes.headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
    },
    onError: (err, req, res) => {
        console.error('Proxy error:', err.message);
        res.status(500).json({ error: 'Proxy error', details: err.message });
    }
}));

// Everything else to Hugo dev server
app.use('/', createProxyMiddleware({
    target: 'http://localhost:1313',
    changeOrigin: true,
    ws: true, // Hugo live-reload websockets
    logLevel: 'debug',
    onProxyReq: (proxyReq, req, res) => {
        console.log('Proxying:', req.method, req.url, '→', proxyReq.path);
    },
    onProxyRes: (proxyRes, req, res) => {
        proxyRes.headers['Access-Control-Allow-Origin'] = '*';
        proxyRes.headers['Cache-Control'] = 'no-cache, no-store, must-revalidate';
    },
    onError: (err, req, res) => {
        console.error('Proxy error:', err.message);
        res.status(500).json({ error: 'Proxy error', details: err.message });
    }
}));

// Return 404 for everything else (Hugo should be accessed directly)
// app.use('/', (req, res) => {
//     res.status(404).send('Use Hugo directly at http://localhost:1313');
// });

const port = 1414; // single origin
app.listen(port, () => {
    console.log(`Dev proxy running at http://localhost:${port}`);
});