from flask import Flask, request, render_template_string

app = Flask(__name__)

TEMPLATE = """
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SimpleApp - HTTP Headers</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            color: #e0e0e0;
            margin: 0;
            padding: 40px;
        }
        h1 {
            color: #00d4ff;
            border-bottom: 2px solid #00d4ff;
            padding-bottom: 10px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .header-table th, .header-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #333;
        }
        .header-table th {
            background: #16213e;
            color: #00d4ff;
            font-weight: 600;
        }
        .header-table tr:hover {
            background: #16213e;
        }
        .header-name {
            color: #ff6b6b;
            font-family: monospace;
            font-size: 14px;
        }
        .header-value {
            color: #a0e7a0;
            font-family: monospace;
            font-size: 13px;
            word-break: break-all;
        }
        .info {
            background: #16213e;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #00d4ff;
        }
    </style>
</head>
<body>
    <h1>🔍 SimpleApp - HTTP Headers Viewer</h1>
    <div class="info">
        <p><strong>Méthode :</strong> {{ method }}</p>
        <p><strong>URL :</strong> {{ url }}</p>
        <p><strong>IP Client :</strong> {{ remote_addr }}</p>
    </div>
    <table class="header-table">
        <thead>
            <tr>
                <th>Header</th>
                <th>Valeur</th>
            </tr>
        </thead>
        <tbody>
            {% for key, value in headers %}
            <tr>
                <td class="header-name">{{ key }}</td>
                <td class="header-value">{{ value }}</td>
            </tr>
            {% endfor %}
        </tbody>
    </table>
</body>
</html>
"""

@app.route('/', defaults={'path': ''})
@app.route('/<path:path>')
def show_headers(path):
    headers = list(request.headers)
    return render_template_string(
        TEMPLATE,
        method=request.method,
        url=request.url,
        remote_addr=request.remote_addr,
        headers=headers
    )

@app.route('/health')
def health():
    return "OK", 200

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
