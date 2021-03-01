var RivetsFormatters = {
    
    // Initializes the formatters.
    init: function(rivets)
    {
        rivets.formatters.date = function(value)
        {
            return moment(value).format('MMM DD, YYYY')
        }
        
        rivets.formatters.duration = function(value)
        {
            var days = 0, hours = 0, minutes = 0, seconds = 0;
            var out = "";
        
            value = value > 86400 ? value - ((days = Math.floor(value / 86400)) * 86400) : value;
            value = value > 3600 ? value - ((hours = Math.floor(value / 3600)) * 3600) : value;
            seconds = value > 60 ? value - ((minutes = Math.floor(value / 60)) * 60) : value;
        
            out += days > 0 ? days + "d " : "";
            out += hours > 0 ? hours + "h " : "";
            out += minutes > 0 ? minutes + "m " : "";
            out += seconds + "s";
        
            return out;
        }
        
        rivets.formatters.twitchChat = function(value)
        {
            return "https://twitch.tv/" + value + "/chat";
        }
    }
};