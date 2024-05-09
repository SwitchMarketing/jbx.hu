
//add event listener to all ajax requests
Ext.Ajax.addListener('requestexception', function(conn , response) {

    var json = null,
        msg = response.responseText;

    if(response.responseJson)    
    {
        msg = response.responseJson;
        json = true;
    }        
    else
    {
        try {
            msg = Ext.JSON.decode(msg);
            json = true;
        }
        catch(e) {
            json = false;
        }
    }
        
    //console.log(msg);
    //console.log(json);

    if(json)
    {        
        if(msg.message)
            msg = msg.message;
        else if(msg.messages)
            msg = msg.messages.message || msg.messages.error;
        else
            msg = 'API HIBA';
    }

    if(response.status !== 418)
    {

        Ext.Msg.alert('HIBA', msg, () => {
            //if(response.status === 401)
                //window.location.reload();
        });

    }    
    
});

//add event listener to all ajax requests
Ext.Ajax.addListener('requestcomplete', function(conn , response) {

    var json = null,
        msg = response;

    try {
        JSON.parse(response.responseText);
        json = true;
    }
    catch(e) {
        json = false;
    }

    if(response && response.responseText && json)
    {
        msg = Ext.JSON.decode(response.responseText);
        if(msg && msg.message)
        {
            if(Ext.os.deviceType == 'Desktop') {
                Ext.toast({
                    message: msg.message,
                    align: 't'
                });
            }
                                
        }            
    }    
    
});