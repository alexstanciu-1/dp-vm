
vagrant plugin install vagrant-timezone

@todo - install events plugin to prevent machine destroy

# https://github.com/stemar/vagrant-ubuntu-22-04/blob/master/settings.yaml

/*
:machine:
  # https://app.vagrantup.com/bento/boxes/ubuntu-22-04
  :box: bento/ubuntu-22.04 # 64GB HDD
  :memory: 3072 # 3GB RAM
  :cpus: 1
  :hostname: ubuntu-22-04
  :timezone: Canada/Pacific

:forwarded_ports:
# SSH
- :id: ssh
  :host: 2200
  :guest: 22
# HTTP
- :host: 8000
  :guest: 80
# MySQL
- :host: 33060
  :guest: 3306

:synced_folder:
  :host: ~/Code
  :guest: /home/vagrant/Code

:copy_files:
- :source: ~/.ssh
  :destination: ~/.ssh
- :source: ~/.gitconfig
  :destination: ~/.gitconfig
*/

