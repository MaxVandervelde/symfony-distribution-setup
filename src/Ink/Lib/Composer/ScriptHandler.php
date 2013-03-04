<?php
/**
 * ScriptHandler.php
 *
 * Post-install hooks for installing Symfony2 with composer
 *
 * @author    Maxwell Vandervelde <Max@MaxVandervelde.com>
 * @version   1.0.0
 * @copyright (c) 2013, Ink Applications
 * @license   MIT
 *            http://opensource.org/licenses/MIT
 */

namespace Ink\Lib\Composer;

use InvalidArgumentException;
use RuntimeException;
use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as SymfonyScriptHandler;

/**
 * ScriptHandler
 *
 * Contains logic to set up post-install hooks for composer to use when setting
 * up Symfony2 with a distribution file set.
 *
 * @author Maxwell Vandervelde <Max@MaxVandervelde.com>
 */
class ScriptHandler extends SymfonyScriptHandler
{
    /**
     * Parameter Files
     *
     * @var array A list of the possible parameters distribution file names, in
     *            The order that they should be looked for.
     */
    protected static $parameterFiles = array(
        'parameters.dist.yml',
        'parameters.dist.xml',
        'parameters.dist.php',
    );

    /**
     * Build Parameters
     *
     * This is a hook for composer to build before Symfony is setup.
     * If no parameters file is included in the project, It copies a parameters
     * distribution file in its place.
     *
     * @param  $event            The composer hook event
     * @throws \RuntimeException Throws on copy fail
     */
    public static function buildParameters($event)
    {
        echo 'Building Parameters File... ';
        $options      = self::getOptions($event);
        $appDir       = $options['symfony-app-dir'];
        $distFile     = self::getParametersDistFile($appDir);
        $distFileInfo = pathinfo($distFile);
        $destination  = $appDir . DIRECTORY_SEPARATOR . 'config'
            . DIRECTORY_SEPARATOR . 'parameters.' . $distFileInfo['extension'];

        if (file_exists($destination)) {
            echo 'Skipping. Parameters already exist' . PHP_EOL;
            return;
        }

        $copyStatus = copy($distFile, $destination);

        if (!$copyStatus) {
            throw new RuntimeException(
                'Could not create parameters. File copy failed at: ' . $destination
            );
        }

        echo 'Success' . PHP_EOL;
    }

    /**
     * Get Parameters File
     *
     * Gets the appropriate parameters distribution file for the Symfony2
     * application based on the first available file
     * as defined in static::$parameterFiles
     *
     * @see    ScriptHandler::$parameterFiles
     * @param  $appDir string            The application directory of Symfony2
     * @return string                    The Parameters Distribution file to use
     * @throws \RuntimeException         Thrown when no dist file is found
     * @throws \InvalidArgumentException Thrown on invalid input
     */
    public static function getParametersDistFile($appDir)
    {
        if (!is_string($appDir)) {
            throw new InvalidArgumentException(
                'First parameter expected a path string'
            );
        }

        $filePrefixPath = $appDir . DIRECTORY_SEPARATOR . 'config';

        foreach (static::$parameterFiles as $file) {
            $filePath = $filePrefixPath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        throw new RuntimeException('Could not find parameters dist file');
    }

    /**
     * Build .htaccess
     *
     * This is a hook for composer to build before Symfony is setup.
     * If no .htaccess file is included in the project, It copies a htaccess
     * distribution file in its place.
     *
     * @param  $event            The composer hook event
     * @throws \RuntimeException Thrown when copy operation fails
     */
    public static function buildHtaccess($event) {
        echo 'Building .htaccess File... ';
        $options      = self::getOptions($event);
        $webDir       = $options['symfony-web-dir'];
        $distFile     = $webDir . DIRECTORY_SEPARATOR . '.dist.htaccess';
        $destination  = $webDir . DIRECTORY_SEPARATOR . '.htaccess';

        if (file_exists($destination)) {
            echo 'Skipping. .htaccess already exists' . PHP_EOL;
            return;
        }

        $copyStatus = copy($distFile, $destination);

        if (!$copyStatus) {
            throw new RuntimeException(
                'Could not create .htaccess. File copy failed at: ' . $destination
            );
        }

        echo 'Success' . PHP_EOL;
    }
}
