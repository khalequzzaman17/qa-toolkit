<?php

namespace QaToolkit;

use Illuminate\Support\ServiceProvider;
use QaToolkit\Console\Commands;
use QaToolkit\Reports\Report;

class QaToolkitServiceProvider extends ServiceProvider
{
    /**
     * All check classes registered with the toolkit. The order here is the
     * order checks run in, and the order they appear in reports.
     */
    public const CHECKS = [
        // Security (~22)
        \QaToolkit\Checks\Security\AppKeySetCheck::class,
        \QaToolkit\Checks\Security\DebugModeCheck::class,
        \QaToolkit\Checks\Security\EnvFileExposedCheck::class,
        \QaToolkit\Checks\Security\CsrfProtectionCheck::class,
        \QaToolkit\Checks\Security\MassAssignmentCheck::class,
        \QaToolkit\Checks\Security\AuthMiddlewareCoverageCheck::class,
        \QaToolkit\Checks\Security\HttpSecurityHeadersCheck::class,
        \QaToolkit\Checks\Security\DefaultCredentialsCheck::class,
        \QaToolkit\Checks\Security\RawUserInputCheck::class,
        \QaToolkit\Checks\Security\ThrottlingMiddlewareCheck::class,
        \QaToolkit\Checks\Security\RouteModelBindingCheck::class,
        \QaToolkit\Checks\Security\FilesystemVisibilityCheck::class,
        \QaToolkit\Checks\Security\CookieSettingsCheck::class,
        \QaToolkit\Checks\Security\BcryptCostCheck::class,
        \QaToolkit\Checks\Security\ClosureRoutesCheck::class,
        \QaToolkit\Checks\Security\DebugBarCheck::class,
        \QaToolkit\Checks\Security\OpenRedirectCheck::class,
        \QaToolkit\Checks\Security\SqlInjectionSinkCheck::class,
        \QaToolkit\Checks\Security\StorageLinkCheck::class,
        \QaToolkit\Checks\Security\MethodOverrideCheck::class,
        \QaToolkit\Checks\Security\HoneypotOrRecaptchaCheck::class,
        \QaToolkit\Checks\Security\EnvKeyRotationCheck::class,
        \QaToolkit\Checks\Security\StrictModeCheck::class,
        \QaToolkit\Checks\Security\PasswordHashCheck::class,
        \QaToolkit\Checks\Security\ComposerAuditCheck::class,
        \QaToolkit\Checks\Security\GdprCookieCheck::class,
        \QaToolkit\Checks\Security\CorsConfigCheck::class,
        \QaToolkit\Checks\Security\DeprecatedHashCheck::class,
        \QaToolkit\Checks\Security\EvalCheck::class,
        \QaToolkit\Checks\Security\DdLeftoverCheck::class,
        \QaToolkit\Checks\Security\SignedRoutesCheck::class,
        \QaToolkit\Checks\Security\ComposerLockFreshnessCheck::class,
        \QaToolkit\Checks\Security\RateLimitingRoutesCheck::class,

        // Performance (~20)
        \QaToolkit\Checks\Performance\CacheDriverCheck::class,
        \QaToolkit\Checks\Performance\SessionDriverCheck::class,
        \QaToolkit\Checks\Performance\QueueDriverCheck::class,
        \QaToolkit\Checks\Performance\LazyLoadingInRoutesCheck::class,
        \QaToolkit\Checks\Performance\ModelStrictModeCheck::class,
        \QaToolkit\Checks\Performance\MissingIndexCheck::class,
        \QaToolkit\Checks\Performance\EagerLoadingCheck::class,
        \QaToolkit\Checks\Performance\RouteCacheConfiguredCheck::class,
        \QaToolkit\Checks\Performance\OpcacheStatusCheck::class,
        \QaToolkit\Checks\Performance\MigrationSizeCheck::class,
        \QaToolkit\Checks\Performance\ChattyLogCheck::class,
        \QaToolkit\Checks\Performance\HorizonOrSupervisorCheck::class,
        \QaToolkit\Checks\Performance\AssetsCompiledCheck::class,
        \QaToolkit\Checks\Performance\ChunkedQueriesCheck::class,
        \QaToolkit\Checks\Performance\MailDriverCheck::class,
        \QaToolkit\Checks\Performance\NPlusOneInViewCheck::class,
        \QaToolkit\Checks\Performance\SchemaForeignKeysCheck::class,
        \QaToolkit\Checks\Performance\OctaneOrSwooleCheck::class,
        \QaToolkit\Checks\Performance\UploadedAssetCdnCheck::class,
        \QaToolkit\Checks\Performance\TelescopeInProdCheck::class,
        \QaToolkit\Checks\Performance\QueryLogDisabledCheck::class,
        \QaToolkit\Checks\Performance\ScheduleCacheCheck::class,

        // Functional (~20)
        \QaToolkit\Checks\Functional\NamedRoutesCheck::class,
        \QaToolkit\Checks\Functional\ControllerResolutionCheck::class,
        \QaToolkit\Checks\Functional\FormValidationCoverageCheck::class,
        \QaToolkit\Checks\Functional\HttpVerbsCoverageCheck::class,
        \QaToolkit\Checks\Functional\ModelScopesCheck::class,
        \QaToolkit\Checks\Functional\AuthRoutesPresentCheck::class,
        \QaToolkit\Checks\Functional\MailTemplatesCheck::class,
        \QaToolkit\Checks\Functional\FallbackRouteCheck::class,
        \QaToolkit\Checks\Functional\MiddlewareAliasesCheck::class,
        \QaToolkit\Checks\Functional\EventListenersCheck::class,
        \QaToolkit\Checks\Functional\JobsHaveTriesCheck::class,
        \QaToolkit\Checks\Functional\ScheduledTasksCheck::class,
        \QaToolkit\Checks\Functional\SeedersExistCheck::class,
        \QaToolkit\Checks\Functional\StorageDirsWritableCheck::class,
        \QaToolkit\Checks\Functional\ControllerDryMethodsCheck::class,
        \QaToolkit\Checks\Functional\BladeCsrfCheck::class,
        \QaToolkit\Checks\Functional\UploadsValidatedCheck::class,
        \QaToolkit\Checks\Functional\TransactionUsageCheck::class,
        \QaToolkit\Checks\Functional\RouteConflictCheck::class,
        \QaToolkit\Checks\Functional\ErrorViewsCheck::class,
        \QaToolkit\Checks\Functional\MailQueueCheck::class,
        \QaToolkit\Checks\Functional\NotificationQueueCheck::class,
        \QaToolkit\Checks\Functional\EmptyRoutesCheck::class,
        \QaToolkit\Checks\Functional\RoutesInRouteFilesCheck::class,

        // Infra (~30)
        \QaToolkit\Checks\Infra\RequiredEnvKeysCheck::class,
        \QaToolkit\Checks\Infra\AppEnvCorrectCheck::class,
        \QaToolkit\Checks\Infra\MigrationsUpToDateCheck::class,
        \QaToolkit\Checks\Infra\CronScheduleEntryCheck::class,
        \QaToolkit\Checks\Infra\QueueWorkerRunningCheck::class,
        \QaToolkit\Checks\Infra\BackupConfiguredCheck::class,
        \QaToolkit\Checks\Infra\GitCleanCheck::class,
        \QaToolkit\Checks\Infra\MaintenanceModeCheck::class,
        \QaToolkit\Checks\Infra\TlsEnabledCheck::class,
        \QaToolkit\Checks\Infra\DbConnectionCheck::class,
        \QaToolkit\Checks\Infra\RedisReachableCheck::class,
        \QaToolkit\Checks\Infra\StoragePublicSymlinkCheck::class,
        \QaToolkit\Checks\Infra\VendorInstalledCheck::class,
        \QaToolkit\Checks\Infra\PhpVersionCheck::class,
        \QaToolkit\Checks\Infra\RequiredPhpExtensionsCheck::class,
        \QaToolkit\Checks\Infra\SentryOrBugsnagCheck::class,
        \QaToolkit\Checks\Infra\StorageFreeSpaceCheck::class,
        \QaToolkit\Checks\Infra\SmtpCredentialsCheck::class,
        \QaToolkit\Checks\Infra\FailedJobsTableCheck::class,
        \QaToolkit\Checks\Infra\FailedJobsBacklogCheck::class,
        \QaToolkit\Checks\Infra\SwaggerOrOpenApiCheck::class,
        \QaToolkit\Checks\Infra\DowntimePageCheck::class,
        \QaToolkit\Checks\Infra\HealthCheckEndpointCheck::class,
        \QaToolkit\Checks\Infra\CharsetCollationCheck::class,
        \QaToolkit\Checks\Infra\LogLevelConfiguredCheck::class,
        \QaToolkit\Checks\Infra\WebServerConfigCheck::class,
        \QaToolkit\Checks\Infra\AssetUrlConfiguredCheck::class,
        \QaToolkit\Checks\Infra\TimezoneCorrectCheck::class,
        \QaToolkit\Checks\Infra\MemoryLimitCheck::class,
        \QaToolkit\Checks\Infra\DatabaseSizeCheck::class,
        \QaToolkit\Checks\Infra\SwaggeredIfApiControllerCheck::class,
        \QaToolkit\Checks\Infra\TrustedProxiesCheck::class,
        \QaToolkit\Checks\Infra\HorizonConfiguredCheck::class,
        \QaToolkit\Checks\Infra\LongRunningMigrationsCheck::class,
        \QaToolkit\Checks\Infra\ConnectionPoolCheck::class,
        \QaToolkit\Checks\Infra\ReadmeExistsCheck::class,
        \QaToolkit\Checks\Infra\AssetCountCheck::class,
        \QaToolkit\Checks\Infra\SslCertValidityCheck::class,
        \QaToolkit\Checks\Infra\BackupFrequencyCheck::class,
        \QaToolkit\Checks\Infra\HorizonSupervisorCheck::class,
        \QaToolkit\Checks\Infra\ObscureEnvKeysCheck::class,
    ];

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/qa-toolkit.php', 'qa-toolkit');

        $this->app->singleton('qa-toolkit.checks', function () {
            $collection = [];
            foreach (self::CHECKS as $class) {
                $collection[$class] = app($class);
            }
            return $collection;
        });

        $this->app->singleton(Report::class, function () {
            return new Report();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/qa-toolkit.php' => config_path('qa-toolkit.php'),
            ], 'qa-toolkit-config');

            $this->publishes([
                __DIR__ . '/../tests/stubs' => base_path('tests/QaToolkitStubs'),
            ], 'qa-toolkit-stubs');

            $this->publishes([
                __DIR__ . '/../ci/github-actions.yml' => base_path('.github/workflows/qa-toolkit.yml'),
            ], 'qa-toolkit-ci');

            $this->commands([
                Commands\QaRunCommand::class,
                Commands\QaSecurityCommand::class,
                Commands\QaPerformanceCommand::class,
                Commands\QaFunctionalCommand::class,
                Commands\QaInfraCommand::class,
                Commands\QaReportCommand::class,
                Commands\QaListChecksCommand::class,
            ]);
        }
    }
}
