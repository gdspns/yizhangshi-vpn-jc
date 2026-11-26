一键安装
curl -fsSL https://raw.githubusercontent.com/gdspns/yizhangshi-vpn-jc/master/install-docker.sh | sudo bash


指定管理员邮箱
curl -fsSL https://raw.githubusercontent.com/gdspns/yizhangshi-vpn-jc/master/install-docker.sh \
  | sudo ADMIN_ACCOUNT=your@email.com bash

  服务器更新使用
  cd /opt/yizhangshi-vpn-jc
sudo docker compose pull
sudo docker compose up -d
