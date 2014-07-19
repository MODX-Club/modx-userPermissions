<?php
if($modx->event->name == 'OnLoadWebDocument'){
    $permissions = array();
    if($modx->user->get('sudo')){
        $permissions =  array(
            'sudo' => true,
        );          
    }else{
        $ctx = $modx->context->get('key');
        $policies = (array)$_SESSION["modx.user.".$modx->user->get('id').".attributes"][$ctx]['modAccessContext'][$ctx];
        foreach($policies as $p){
            $permissions = array_merge($permissions, (array)$p['policy']);
        }
        $permissions['sudo'] = false;
    }
    
    $modx->regClientStartupHTMLBlock('
        <script type="text/javascript">
            (function(exports){
                var UserPermissions = {
                    _permissions : '. $modx->toJSON($permissions).'
                    ,checkPolicy: function(policy,callback,params){
                        // params = params || {};
                        var args = Array.prototype.splice.call(arguments,1);
    
                        if(UserPermissions._permissions.sudo || UserPermissions._permissions[policy]){
                            return this.success.apply(this,args);        
                        }else{
                            return this.failure.apply(this,args);     
                        }
                    }
                    ,success:function(callback,params){
                        if(callback && typeof callback.s === "function"){
                            callback.s(params);
                        }
                        return true;
                    }
                    ,failure:function(callback,params){
                        if(callback && typeof callback.f === "function"){
                            callback.f(params);
                        }
                        return false;
                    }
                }
                exports.UserPermissions = UserPermissions;
            })(window);
        </script>
    ');
};