from typing import Dict, Type, Any

class StudioRegistry:
    """
    Central registry for all Studio Widgets, Dashboards, and Services.
    Ensures that components can be dynamically discovered by the frontend.
    """
    _widgets: Dict[str, Type[Any]] = {}
    _services: Dict[str, Any] = {}
    
    @classmethod
    def register_widget(cls, widget_id: str, widget_class: Type[Any]):
        cls._widgets[widget_id] = widget_class
        
    @classmethod
    def register_service(cls, service_id: str, service_instance: Any):
        cls._services[service_id] = service_instance
        
    @classmethod
    def get_widget(cls, widget_id: str) -> Type[Any]:
        return cls._widgets.get(widget_id)
        
    @classmethod
    def get_service(cls, service_id: str) -> Any:
        return cls._services.get(service_id)
