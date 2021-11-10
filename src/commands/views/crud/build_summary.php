To add the module to admin menu, update your 'Module.php' like this:

public $apis = [
    '<?= $apiEndpoint; ?>' => '<?= $apiClassPath; ?>',
];

public function getMenu()
{
    return (new \luya\admin\components\AdminMenuBuilder($this))
        ->node('<?= $humanizeModelName; ?>', 'extension')
            ->group('Group')
                ->itemApi('<?= $humanizeModelName; ?>', '<?= $controllerRoute; ?>', 'label', '<?= $apiEndpoint; ?>');
}
