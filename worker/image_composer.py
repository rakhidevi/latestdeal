# pyrefly: ignore [missing-import]
from PIL import Image, ImageDraw, ImageFont
import io
import base64
import requests

def compose_image(image_url: str, original_price: float, discounted_price: float) -> str:
    """
    Downloads the product image, overlays the price/discount badge locally,
    and returns it as a base64 encoded string to send to the server.
    Offloading this to the Local Worker saves server CPU (cPanel limitations).
    """
    try:
        # 1. Download image
        response = requests.get(image_url, stream=True)
        response.raise_for_status()
        base_img = Image.open(response.raw).convert("RGBA")
        
        # 2. Resize and prepare canvas (Simplified Example)
        base_img = base_img.resize((800, 800))
        # We removed the ugly red text burning onto the image.
        # Modern UI will handle badges in HTML/CSS.
        
        # 4. Convert to base64
        buffered = io.BytesIO()
        base_img.convert("RGB").save(buffered, format="JPEG", quality=85)
        img_str = base64.b64encode(buffered.getvalue()).decode("utf-8")
        
        return f"data:image/jpeg;base64,{img_str}"
        
    except Exception as e:
        print(f"Error composing image: {e}")
        return ""
