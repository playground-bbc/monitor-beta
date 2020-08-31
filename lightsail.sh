#!/bin/bash

# install latest version of docker the lazy way
curl -sSL https://get.docker.com | sh
# install composer
sudo apt install composer

# make it so you don't need to sudo to run docker commands
sudo usermod -aG docker ubuntu

# install docker-compose
curl -L https://github.com/docker/compose/releases/download/1.21.2/docker-compose-$(uname -s)-$(uname -m) -o /home/ubuntu/docker-compose
chmod +x /home/ubuntu/docker-compose

# copy the dockerfile into /srv/docker 
# if you change this, change the systemd service file to match
# WorkingDirectory=[whatever you have below]
mkdir /home/ubuntu/monitor
cd /home/ubuntu/monitor
git clone https://github.com/playground-bbc/monitor-beta.git .

cd ..
# copy in systemd unit file and register it so our compose file runs 
# on system restart
sudo curl -o /home/ubuntu/docker-compose-app.service https://raw.githubusercontent.com/mikegcoleman/todo/master/docker-compose-app.service
sudo systemctl enable docker-compose-app

# start up the application via docker-compose
./docker-compose -f /home/ubuntu/monitor/docker-compose.yml up -d