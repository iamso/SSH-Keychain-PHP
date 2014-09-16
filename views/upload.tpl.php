set -e
keypath="<?=$this->keypath?>"
if [[ $keypath = "" ]]
then
	if [ -e "$HOME/.ssh/id_dsa.pub" ]
	then
		keypath="$HOME/.ssh/id_dsa.pub"
	elif [ -e "$HOME/.ssh/id_rsa.pub" ]
	then
		keypath="$HOME/.ssh/id_rsa.pub"
	else
		echo -e "\\033[31mUnable to find a public key.\\033[0m" 
		echo "Specify a path with 'keypath' parameter in URL."
		exit 1
	fi
elif [ ! -f "$keypath" ]
then
	echo -e "\\033[31mUnable to find public key.\\033[0m" 
	echo "Specify a correct path with 'keypath' parameter in URL."
	exit 1
fi
curl -sk -H "Content-Type: application/json" -X PUT -d '{"key":"'"$(cat $keypath)"'"}' "<?=$this->url?>"
