<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Notification</title>

    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            overflow: hidden;
        }

        .header {
            background: #6b46c1;
            color: #ffffff;
            padding: 16px 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
        }

        .content {
            padding: 20px;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }

        .label {
            font-weight: bold;
            margin-top: 15px;
            display: block;
        }

        .box {
            background: #f7f7f7;
            border: 1px solid #e5e5e5;
            padding: 12px;
            border-radius: 4px;
            margin-top: 6px;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .meta {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 8px;
            margin-top: 15px;
            font-size: 13px;
        }

        .meta div {
            padding: 4px 0;
        }

        .footer {
            padding: 12px 20px;
            background: #fafafa;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="header">
        <h2>🚨 System Notification</h2>
    </div>

    <div class="content">

        <div class="meta">
            <div><strong>Sent at</strong></div>
            <div>{{ now() }}</div>

            <div><strong>URL</strong></div>
            <div>{{ $url ?? '-' }}</div>

            <div><strong>Method</strong></div>
            <div>{{ $method ?? '-' }}</div>

            <div><strong>IP</strong></div>
            <div>{{ $ip ?? '-' }}</div>
        </div>

        <span class="label">Message</span>
        <div class="box">
            {{ $exceptionMessage ?? 'No message provided' }}
        </div>

        <span class="label">Stack Trace</span>
        <div class="box">
            {{ $trace ?? 'No trace available' }}
        </div>

    </div>

    <div class="footer">
        {{ config('app.name') }} — Automated system email
    </div>

</div>

</body>
</html>
