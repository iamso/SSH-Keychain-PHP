How to use
----------

upload and install your public ssh key with ease.

// help
<?=$this->url?><?=$this->email?>/help

// upload
curl -sk <?=$this->url?><?=$this->email?>/upload | bash

// install
curl -sk <?=$this->url?><?=$this->email?>/install | bash

// fingerprint
curl -sk <?=$this->url?><?=$this->email?>/fingerprint
