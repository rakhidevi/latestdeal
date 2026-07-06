FROM python:3.11-slim

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y gcc

# Keep container running indefinitely so you can bash in or run tests locally
# Alternatively, you can change this to CMD ["python", "main.py"]
CMD ["tail", "-f", "/dev/null"]
