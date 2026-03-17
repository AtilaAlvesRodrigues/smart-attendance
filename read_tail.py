import sys
with open(sys.argv[1], 'r', encoding='utf-8', errors='replace') as f:
    lines = f.readlines()
    print(''.join(lines[-150:]))
