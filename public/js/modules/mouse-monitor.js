var MouseMonitor = {
    mousePosition: {x: 0, y:0},
    
    init: function()
    {
        this.bindUIActions();
    },
    
    /**
     * Checks if the mouse is currently inside of the bounding rect of another element
     */
    mouseInElement: function(element)
    {
        var boundingRect = element.getBoundingClientRect();
        
        if(this.mousePosition.x >= boundingRect.left && this.mousePosition.x <= (boundingRect.left + element.clientWidth)
        && this.mousePosition.y >= boundingRect.top && this.mousePosition.y <= (boundingRect.top + element.clientHeight))
            return true;
        else
            return false;
    },
    
    /**
     * Gets the current mouse position
     */
    getMousePosition: function()
    {
        return {x: this.mousePosition.x, y: this.mousePosition.y};
    },
    
    /**
     * Binds events to UI elements.
     */
    bindUIActions: function()
    {
        $(document).on('mousemove', this.onMouseMove.bind(this));
        $(document).on('dragover', this.onMouseMove.bind(this));
    },
    
    /**
     * Event: Mouse move, register new cursor position.
     */
    onMouseMove: function(ev)
    {
        this.mousePosition.x = ev.clientX;
        this.mousePosition.y = ev.clientY;
    }
};