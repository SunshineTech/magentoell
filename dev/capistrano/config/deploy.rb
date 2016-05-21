lock '3.4.0'

set :application,   'ellison'
set :repo_url,      'git@github.com:separationdegrees/ellison.git'
set :keep_releases, 5

app_symlinks = ['app/etc/local.xml', 'errors/local.xml', 'erp', 'images', 'media', 'shell/images', 'var', 'wp/wp-config.php', 'wp/wp-content/uploads']

namespace :magento do
    desc 'Symlink shared files and folders'
    task :apply_symlinks do
        on roles(:app) do
            app_symlinks.each do |symlink|
                execute "rm -rf #{release_path}/#{symlink}"
                execute "ln -s #{shared_path}/#{symlink} #{release_path}/#{symlink}"
            end
        end
    end
    desc 'Generate CSS'
    task :generate_css do
        on roles(:app) do
            execute "#{release_path}/shell/compass.sh clean"
            execute "#{release_path}/shell/compass.sh compile -e develop"
        end
    end
    desc 'Enable maintenance mode'
    task :maint_on do
        on roles(:app) do
            execute "touch #{release_path}/maintenance.flag"
        end
    end
    desc 'Disable maintenance mode'
    task :maint_off do
        on roles(:app) do
            execute "rm -f #{release_path}/maintenance.flag"
        end
    end
end

namespace :hipchat do
    desc 'Hipchat Notification'
    task :notify do
        sh "curl -sSd '{\"color\": \"green\", \"message\": \"Ellison: #{revision_log_message}\", \"notify\": true, \"message_format\": \"text\"}' -H 'Content-Type: application/json' https://api.hipchat.com/v2/room/1738188/notification?auth_token=QbCmHqvh3OBTgRIOmg0qjC1Kb36QEO757GRzSZl3"
    end
end

after 'deploy:updated', 'magento:apply_symlinks'
after 'deploy:updated', 'magento:generate_css'
after 'deploy:updated', 'deploy:cleanup'

after 'deploy', 'hipchat:notify'
