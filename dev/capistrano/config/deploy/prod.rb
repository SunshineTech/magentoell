server '54.153.109.220', user: 'deploy', roles: %w{app web}
server '52.9.44.109', user: 'deploy', roles: %w{app web}

set :branch,    'master'
set :deploy_to, '/var/www/html'

namespace :newrelic do
    desc 'New relic Notification'
    task :notify do
        sh "curl -sS -H \"x-api-key:547f4e79211a0d4f06e498d220822d98ef966c842ce6b33\" -d \"deployment[application_id]=8399103\" -d \"deployment[description]=#{revision_log_message}\" https://api.newrelic.com/deployments.xml > /dev/null"
    end
end

namespace :magento do
    desc 'Flush Magento cache'
    task :purgecache do
        on roles(:app) do
            execute "magerun --root-dir=#{release_path} cache:flush"
        end
    end
    desc 'Full reindex'
    task :reindexall do
        on roles(:app) do
            execute "magerun --root-dir=#{release_path} index:reindex:all"
        end
    end
end

namespace :nginx do
    desc 'Reload Nginx'
    task :reload do
        on roles(:app) do
            execute "sudo /home/deploy/reload-nginx.sh"
        end
    end
end

namespace :pagespeed do
    desc 'Clear Pagespeed cache'
    task :purgecache do
        on roles(:app) do
            execute "rm -rf /var/ngx_pagespeed_cache/*"
        end
    end
end

after 'deploy', 'newrelic:notify'
#after 'deploy', 'magento:purgecache'
#after 'deploy', 'pagespeed:purgecache'
#after 'deploy', 'nginx:reload'
