import os
import re

directory = r'c:\Users\Adm\Documents\Codigos\PHP\Projeto integrador\Attendance\resources\views'

patterns = [
    r'rounded-3xl',
    r'rounded-2xl',
    r'rounded-xl',
    r'rounded-lg',
    r'rounded-md',
    r'rounded-\[2\.5rem\]',
    r'rounded-\[3rem\]'
]

for root, _, files in os.walk(directory):
    for file in files:
        if file.endswith('.blade.php') and 'layouts' not in root:
            filepath = os.path.join(root, file)
            with open(filepath, 'r', encoding='utf-8') as f:
                content = f.read()
            
            new_content = content
            for pattern in patterns:
                new_content = re.sub(r'\b' + pattern + r'\b', 'rounded-sm', new_content)
            
            if new_content != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f"Updated: {filepath}")
