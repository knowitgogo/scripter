<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} — API Docs</title>
    <link rel="stylesheet" href="{{ sprintf('https://unpkg.com/swagger-ui-dist@%s/swagger-ui.css', $cdnVersion) }}">
</head>
<body>
<div id="swagger-ui"></div>
<script src="{{ sprintf('https://unpkg.com/swagger-ui-dist@%s/swagger-ui-bundle.js', $cdnVersion) }}"></script>
<script src="{{ sprintf('https://unpkg.com/swagger-ui-dist@%s/swagger-ui-standalone-preset.js', $cdnVersion) }}"></script>
<script>
    window.onload = function () {
        SwaggerUIBundle({
            url: {!! json_encode($specUrl, JSON_UNESCAPED_SLASHES) !!},
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
                SwaggerUIStandalonePreset,
            ],
            layout: 'StandaloneLayout',
        });
    };
</script>
</body>
</html>
