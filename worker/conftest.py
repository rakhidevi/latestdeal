import sys
import os

# Ensure the parent directory is in the Python path so that 'worker.xyz' imports work during pytest
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
