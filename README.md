# ezplatform-youtube-fieldtype
Bundle provides YouTube Embed field type for eZPlatform 1.8 and higher.

### Installation
Package is available in composer repository, so installation is very simple.

1. Require package in composer:
```
composer require kmadejski/ezplatform-youtube-fieldtype:dev-master
```

2. Register bundle in your `app/AppKernel.php`:
```
$bundles = array(
...
new EzSystems\YouTubeFieldTypeBundle\EzSystemsYouTubeFieldTypeBundle(),
...
);
```

3. Install assets (from your project root):
```
php app/console assets:install --symlink --relative
```