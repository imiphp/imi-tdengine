#!/bin/bash
wget -qO - http://repos.taosdata.com/tdengine.key | sudo apt-key add - && \
echo "deb [arch=amd64] http://repos.taosdata.com/tdengine-stable stable main" | sudo tee /etc/apt/sources.list.d/tdengine-stable.list && \
apt update && \
apt install tdengine=${TDENGINE_VERSION} && \
systemctl start taosd
systemctl start taosadapter

sleep 5
taos -s "create database db_test"
