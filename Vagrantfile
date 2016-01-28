Vagrant.configure(2) do |config|
  config.ssh.forward_agent = true
  config.ssh.insert_key = false
  config.ssh.private_key_path = ['~/.vagrant.d/insecure_private_key', '~/.ssh/id_rsa']

  if Vagrant.has_plugin?("vagrant-cachier")
    config.cache.scope = :machine
  end

  config.vm.define "p4lcomodo" do |box|
    # guest os
    box.vm.box = "ubuntu/trusty64"
    box.vm.box_url = "https://cloud-images.ubuntu.com/vagrant/trusty/current/trusty-server-cloudimg-amd64-vagrant-disk1.box"
    box.vm.hostname = "p4lcomodo"

    # network
    box.vm.network :forwarded_port, guest: 80, host: 8080, auto_correct: true

    # virtualbox
    box.vm.provider :virtualbox do |vbox, override|
      override.vm.synced_folder "vagrant", "/vagrant", type: "nfs"
      override.bindfs.bind_folder "/vagrant", "/vagrant", :owner => 1000, :group => 33
      override.vm.network :private_network, type: "dhcp"
      vbox.customize ["modifyvm", :id, "--memory", 2048]
      vbox.customize ["modifyvm", :id, "--cpus", 2]
      vbox.customize ["modifyvm", :id, "--name", "p4lcomodo"]
    end

    # libvirt configuration
    box.vm.provider :libvirt do |libvirt, override|
      override.vm.box = "baremettle/ubuntu-14.04"
      override.vm.box_url = "https://atlas.hashicorp.com/baremettle/boxes/ubuntu-14.04/versions/1.0.0/providers/libvirt.box"
      override.vm.synced_folder "vagrant", "/vagrant", type: "nfs"
      override.bindfs.bind_folder "/vagrant", "/vagrant", :owner => 1000, :group => 33
      #override.vm.network :private_network, :ip => "192.168.121.2"
      libvirt.memory = 2048
      libvirt.cpus = 2
      libvirt.nested = true
    end

    # lxc configuration
    box.vm.provider :lxc do |lxc, override|
      override.vm.box = "fgrehm/trusty64-lxc"
      override.vm.box_url = "https://atlas.hashicorp.com/fgrehm/boxes/trusty64-lxc/versions/1.2.0/providers/lxc.box"
      #override.vm.network :private_network, :ip => "192.168.121.2", lxc__bridge_name: "vlxcbr1"
      lxc.customize "cgroup.memory.limit_in_bytes", "2048M"
      lxc.customize "mount.auto", "cgroup"
      lxc.customize "aa_profile", "unconfined"
      lxc.customize "cgroup.devices.allow", "a"
    end

    box.vm.provision :shell, :inline => <<-HEREDOC
      export DEBIAN_FRONTEND=noninteractive
      apt-get -q -y update
      apt-get -q -y --force-yes install \
        acl curl git htop screen sendmail sysv-rc-conf vim zip unzip p7zip-full \
        aptitude bash-completion bzip2 landscape-common less locate man \
        apache2-dev libapache2-mod-php5 \
        php5-apcu php5-cli php5-common php5-curl php5-dev php5-gd php5-imap php5-intl \
        php5-json php5-mcrypt php5-readline php5-tidy php5-xdebug php5-xsl
      perl -i -pe 's/\\/var\\/www\\/html/\\/vagrant\\/public/g' /etc/apache2/sites-enabled/000-default.conf
      perl -i -pe 's/\\/var\\/www/\\/vagrant/g' /etc/apache2/apache2.conf
      echo "; load as last item to override other ini settings
; priority=99

[core]
date.timezone = Europe/London
date.default_latitude = 51.500181
date.default_longitude = -0.12619
error_reporting = E_ALL
expose_php = 0
mail.add_x_header = 0
max_execution_time = 600
memory_limit = 256M
post_max_size = 20M
session.cookie_httponly = 1
session.gc_maxlifetime = 3600
short_open_tag = 1
upload_max_filesize = 20M

; debug
display_errors = 1
session.cookie_secure = 0

[xdebug]
xdebug.remote_enable = 1
xdebug.profiler_enable_trigger = 1
xdebug.profiler_output_name = cachegrind.out.%t
xdebug.remote_connect_back = 1" | tee /etc/php5/mods-available/dev.ini >/dev/null
      php5enmod dev
      apache2ctl graceful
      curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
      wget -q http://download.comodo.com/cavmgl/download/installs/1000/standalone/cav-linux_1.1.268025-1_amd64.deb
      dpkg -i cav-linux_1.1.268025-1_amd64.deb
      apt-get -q -y --force-yes install
      wget -q http://download.comodo.com/av/updates58/sigs/bases/bases.cav -O /opt/COMODO/scanners/bases.cav
      /opt/COMODO/post_setup.sh
    HEREDOC

    box.vm.provision :shell, :privileged => false, :inline => <<-HEREDOC
      ROOT_SSH_AUTH_SOCK=$(ls -lt1 /tmp/ssh-*/agent.* | awk '$3 == "root" {print $9}' | head -n1)
      echo Changing SSH_AUTH_SOCK=$ROOT_SSH_AUTH_SOCK
      [[ -f $ROOT_SSH_AUTH_SOCK ]] && sudo chmod 777 $ROOT_SSH_AUTH_SOCK && export SSH_AUTH_SOCK=$ROOT_SSH_AUTH_SOCK
      cd /vagrant
      composer install
      composer generate-hydrators
    HEREDOC
  end
end
