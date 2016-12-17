server '46.101.15.23', user: 'serverpilot', port: 22, roles: [:web, :app, :db], primary: true

set :repo_url,        'git@github.com:MaxHogervorst/lords.git'
set :application,     'lords'
set :user,            'serverpilot'
set :local_user,          "serverpilot"

set :repository_cache, "git_cache"
set :deploy_via, :remote_cache

set :use_sudo,        truedd

set :deploy_via,      :copy
set :deploy_to,       "/srv/users/serverpilot/apps/lords2"

set :default_env, { path: "/usr/local/bin:$PATH" }
set :composer_install_flags, ' --no-interaction --quiet --optimize-autoloader'

## Defaults:
set :scm,           :git
# set :branch,        :master
# set :format,        :pretty
set :log_level,     3
 set :keep_releases, 5

# Which roles to consider as laravel roles
set :laravel_roles, :all

# The artisan flags to include on artisan commands by default
set :laravel_artisan_flags, "--env=#{fetch(:stage)}"

# Which roles to use for running migrations
set :laravel_migration_roles, :all

# The artisan flags to include on artisan commands by default when running migrations
set :laravel_migration_artisan_flags, "--force --env=#{fetch(:stage)}"

# The version of laravel being deployed
set :laravel_version, 5.3

# Which dotenv file to transfer to the server
set :laravel_dotenv_file, './.env'

# The user that the server is running under (used for ACLs)
set :laravel_server_user, 'serverpilot'

# Ensure the dirs in :linked_dirs exist?
set :laravel_ensure_linked_dirs_exist, true

# Link the directores in laravel_linked_dirs?
set :laravel_set_linked_dirs, true


# Linked directories for a standard Laravel 5 application
set :laravel_5_linked_dirs, [
  'storage',
  'vendor',
  'bootstrap/cache',
]

# Ensure the paths in :file_permissions_paths exist?
set :laravel_ensure_acl_paths_exist, true

# Set ACLs for the paths in laravel_acl_paths?
set :laravel_set_acl_paths, true

# Paths that should have ACLs set for a standard Laravel 5 application
set :laravel_5_acl_paths, [
  'bootstrap/cache',
  'storage',
  'storage/app',
  'storage/app/public',
  'storage/framework',
  'storage/framework/cache',
  'storage/framework/sessions',
  'storage/framework/views',
  'storage/logs',
]

namespace :laravel do

    desc "Run Laravel Artisan migrate task."
    task :migrate do
        on roles(:app), in: :sequence, wait: 5 do
            within release_path  do
                execute :php, "artisan migrate"
            end
        end
    end

    desc "Run Laravel Artisan seed task."
    task :seed do
        on roles(:app), in: :sequence, wait: 5 do
            within release_path  do
                execute :php, "artisan db:seed"
            end
        end
    end
end

namespace :deploy do
    after :published, "laravel:migrate"
    # after :published, "laravel:seed"

end