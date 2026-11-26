1. 环境准备
Install Docker:  安装 Docker：

curl -sSL https://get.docker.com | bash

# For CentOS systems, also run:
systemctl enable docker
systemctl start docker

2. 一键安装

curl -fsSL https://raw.githubusercontent.com/gdspns/yizhangshi-vpn-jc/master/install-docker.sh | sudo bash

3.Install database:  安装数据库：
Quick installation (Recommended for beginners)
快速安装（推荐给初学者）

docker compose run -it --rm \
    -e ENABLE_SQLITE=true \
    -e ENABLE_REDIS=true \
    -e ADMIN_ACCOUNT=admin@demo.com \
    xboard php artisan xboard:install

    
指定管理员邮箱
curl -fsSL https://raw.githubusercontent.com/gdspns/yizhangshi-vpn-jc/master/install-docker.sh \
  | sudo ADMIN_ACCOUNT=your@email.com bash

  服务器更新使用
  cd /opt/yizhangshi-vpn-jc
sudo docker compose pull
sudo docker compose up -d

默认端口：7001
