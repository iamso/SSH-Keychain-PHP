mkdir -p $HOME/.ssh
chmod 700 $HOME/.ssh
touch $HOME/.ssh/authorized_keys
chmod 600 $HOME/.ssh/authorized_keys
echo "<?=$this->key?>" >> $HOME/.ssh/authorized_keys
