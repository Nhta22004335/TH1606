import os
import sys
import json
import warnings

# --- Thiết lập thư mục config writable trước khi import YOLO ---
yolo_config_dir = "/var/www/html/tmp_ultralytics"
os.makedirs(yolo_config_dir, exist_ok=True)
os.chmod(yolo_config_dir, 0o777)
os.environ["YOLO_CONFIG_DIR"] = yolo_config_dir

# --- Bây giờ import YOLO ---
from ultralytics import YOLO
warnings.filterwarnings("ignore")

# --- Lấy đường dẫn ảnh ---
if len(sys.argv) < 2:
    print(json.dumps({"error": "No image path provided"}))
    sys.exit(1)

image_path = sys.argv[1]

try:
    # Load model YOLO
    model = YOLO("/var/www/html/yolov8n.pt")

    # Dự đoán
    results = model.predict(image_path)

    # Lấy nhãn và confidence
    output = []
    for result in results:
        for box in result.boxes:
            cls = int(box.cls[0])
            conf = float(box.conf[0])
            label = model.names[cls]
            output.append({"label": label, "confidence": conf})

    print(json.dumps(output))

except Exception as e:
    print(json.dumps({"error": str(e)}))
