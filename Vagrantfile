# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|

 	config.vm.provider :virtualbox do |v|
		v.name = "catalyst-coding-challenge"
		v.customize [
		    "modifyvm", :id,
		    "--memory", 512,
		    "--natdnshostresolver1", "on",
		    "--cpus", 1,
		    "--cpuexecutioncap", 50,
		    "--pae", "on",
		    "--hpet", "on",
		    "--hwvirtex", "on",
		    "--nestedpaging", "on",
		    "--largepages", "on",
		    "--vtxvpid", "on",
		    "--vtxux", "on",
		]
	end

	config.vm.box = "ubuntu/xenial64"
	config.vm.network "forwarded_port", guest: 80, host: 8081
	config.vm.network "private_network", ip: "192.168.33.10"
	config.vm.hostname = "catalyst-coding-challenge.ollie"
	config.vm.synced_folder './', '/vagrant', nfs: true

	config.vm.provision :shell, :path => "scripts/provision.sh"
	config.vm.provision :shell, :path => "scripts/mysql.sh"
	config.vm.provision :shell, :path => "scripts/heroku.sh"
end