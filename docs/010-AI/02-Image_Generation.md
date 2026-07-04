# Image Generation (Compositing)

**ID:** REQ-AI-002
**Status:** Completed
**Last Updated:** 2026-06-29

## Requirement
Publishers need attractive images to post. Raw scraped images are often boring white backgrounds.

## Implementation (PHP GD / Imagick)
Instead of paying for expensive AI image generation (Midjourney/DALL-E) for every deal, we will use programmatic compositing:
1. Download the raw product image.
2. Crop it to a 1:1 square.
3. Overlay a vibrant "X% OFF!" badge in the top right corner.
4. Overlay a subtle border.
5. Save to local storage and pass the URL to the Publishing Engine.
