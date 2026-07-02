<?php

namespace App\Providers;

use App\Http\Responses\CustomAuthenticatedSessionResponse;
use App\Http\Responses\CustomVerifyEmailViewResponse;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\CategoryRepositoryInterface;
use App\Repositories\Transaction\TransactionRepository;
use App\Repositories\Transaction\TransactionRepositoryInterface;
use App\Repositories\Transfer\TransferRepository;
use App\Repositories\Transfer\TransferRepositoryInterface;
use App\Repositories\User\UserRepository;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\Wallet\WalletRepository;
use App\Repositories\Wallet\WalletRepositoryInterface;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(VerifyEmailViewResponse::class, function ($app) {
            return new CustomVerifyEmailViewResponse;
        });

        $this->app->singleton(LoginResponse::class, function ($app) {
            return new CustomAuthenticatedSessionResponse;
        });

        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
        $this->app->bind(TransferRepositoryInterface::class, TransferRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
