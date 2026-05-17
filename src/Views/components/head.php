<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Views\Components
 * @file Common HTML head component
 *
 * Usage: render_head($title, $extraCss = [], $extraJs = [])
 */

/**
 * Render common HTML head section
 *
 * @param string $title Page title
 * @param array $extraCss Additional CSS file paths (relative to root)
 * @param array $extraJs Additional JS file paths (relative to root)
 * @param string $rootPath Root path prefix (e.g., '.' or '..')
 * @return void
 */
function render_head(string $title, array $extraCss = [], array $extraJs = [], string $rootPath = '.', string $inlineStyles = ''): void
{
    $cssFiles = array_merge([
        'css/design-system.css',
        'css/components.css',
        'css/layout.css',
        'css/navigation.css',
        'css/utilities.css',
    ], $extraCss);

    $prefixPath = function (string $path) use ($rootPath): string {
        if (str_starts_with($path, '//') || str_starts_with($path, 'http')) {
            return $path;
        }
        return $rootPath . '/' . $path;
    };

?>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <title><?php echo e($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<?php foreach ($cssFiles as $css): ?>
    <link rel="stylesheet" href="<?php echo $prefixPath($css); ?>">
<?php endforeach; ?>
<?php foreach ($extraJs as $js): ?>
    <script src="<?php echo $prefixPath($js); ?>"></script>
<?php endforeach; ?>
<?php if ($inlineStyles !== ''): ?>
    <style><?php echo $inlineStyles; ?></style>
<?php endif; ?>
</head>
<?php
}
