<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitac40a31d19e5e2d23c17bd07e38a6a17
{
    public static $prefixLengthsPsr4 = array (
        'U' => 
        array (
            'Utils\\' => 6,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'D' => 
        array (
            'Datebase\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Utils\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Utils',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Datebase\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/Dabebase',
        ),
    );

    public static $prefixesPsr0 = array (
        'P' => 
        array (
            'PHPExcel' => 
            array (
                0 => __DIR__ . '/..' . '/phpoffice/phpexcel/Classes',
            ),
        ),
    );

    public static $classMap = array (
        'EasyPeasyICS' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/EasyPeasyICS.php',
        'PHPMailer' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
        'PHPMailerOAuth' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauth.php',
        'PHPMailerOAuthGoogle' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmaileroauthgoogle.php',
        'POP3' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.pop3.php',
        'SMTP' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.smtp.php',
        'ntlm_sasl_client_class' => __DIR__ . '/..' . '/phpmailer/phpmailer/extras/ntlm_sasl_client.php',
        'phpmailerException' => __DIR__ . '/..' . '/phpmailer/phpmailer/class.phpmailer.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitac40a31d19e5e2d23c17bd07e38a6a17::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitac40a31d19e5e2d23c17bd07e38a6a17::$prefixDirsPsr4;
            $loader->prefixesPsr0 = ComposerStaticInitac40a31d19e5e2d23c17bd07e38a6a17::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitac40a31d19e5e2d23c17bd07e38a6a17::$classMap;

        }, null, ClassLoader::class);
    }
}
