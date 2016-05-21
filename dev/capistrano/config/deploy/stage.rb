server '54.67.55.111', user: 'deploy', roles: %w{app web}
set :branch,    'stage'
set :deploy_to, '/var/www/ellison_stage'

namespace :newrelic do
    desc 'New relic Notification'
    task :notify do
        sh "curl -sS -H \"x-api-key:547f4e79211a0d4f06e498d220822d98ef966c842ce6b33\" -d \"deployment[application_id]=8399352\" -d \"deployment[description]=#{revision_log_message}\" https://api.newrelic.com/deployments.xml > /dev/null"
    end
end

after 'deploy', 'newrelic:notify'
