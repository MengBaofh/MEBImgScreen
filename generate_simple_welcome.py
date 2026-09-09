#!/usr/bin/env python3
"""
生成简单的欢迎图片 (不依赖 Pillow)
使用纯 Python 生成 PNG 格式图片
"""

import os
import struct
import zlib

def create_png(width, height, pixels):
    """
    创建 PNG 图片
    pixels: list of (r, g, b) tuples for each pixel
    """
    def pack_chunk(chunk_type, data):
        chunk_data = chunk_type + data
        crc = zlib.crc32(chunk_data) & 0xffffffff
        return struct.pack('>I', len(data)) + chunk_data + struct.pack('>I', crc)

    # PNG signature
    png_data = b'\x89PNG\r\n\x1a\n'

    # IHDR chunk
    ihdr_data = struct.pack('>IIBBBBB', width, height, 8, 2, 0, 0, 0)
    png_data += pack_chunk(b'IHDR', ihdr_data)

    # IDAT chunk - image data
    raw_data = b''
    for y in range(height):
        raw_data += b'\x00'  # filter type
        for x in range(width):
            idx = y * width + x
            if idx < len(pixels):
                r, g, b = pixels[idx]
                raw_data += struct.pack('BBB', r, g, b)
            else:
                raw_data += b'\x00\x00\x00'

    compressed_data = zlib.compress(raw_data, 9)
    png_data += pack_chunk(b'IDAT', compressed_data)

    # IEND chunk
    png_data += pack_chunk(b'IEND', b'')

    return png_data

def create_welcome_simple():
    """创建 256x256 欢迎图片"""
    width, height = 256, 256
    pixels = []

    for y in range(height):
        for x in range(width):
            # 渐变背景 (蓝色系)
            r = 30
            g = 30 + y // 2
            b = 100 + y // 2
            pixels.append((r, g, b))

    # 简单的文字效果 (使用像素块)
    # "WELCOME" 在中间位置
    text_start_y = 100
    text_start_x = 70

    # 简化的字母 W
    for y in range(text_start_y, text_start_y + 20):
        for x in [text_start_x, text_start_x + 4, text_start_x + 8, text_start_x + 12, text_start_x + 16]:
            if 0 <= y < height and 0 <= x < width:
                idx = y * width + x
                if idx < len(pixels):
                    pixels[idx] = (255, 215, 0)  # 金黄色

    # E
    ex = text_start_x + 20
    for y in range(text_start_y, text_start_y + 20):
        if 0 <= y < height and 0 <= ex < width:
            idx = y * width + ex
            if idx < len(pixels):
                pixels[idx] = (255, 215, 0)
    for x in range(ex, ex + 12):
        for yy in [text_start_y, text_start_y + 10, text_start_y + 19]:
            if 0 <= yy < height and 0 <= x < width:
                idx = yy * width + x
                if idx < len(pixels):
                    pixels[idx] = (255, 215, 0)

    # L
    lx = text_start_x + 35
    for y in range(text_start_y, text_start_y + 20):
        if 0 <= y < height and 0 <= lx < width:
            idx = y * width + lx
            if idx < len(pixels):
                pixels[idx] = (255, 215, 0)
    for x in range(lx, lx + 12):
        if 0 <= text_start_y + 19 < height and 0 <= x < width:
            idx = (text_start_y + 19) * width + x
            if idx < len(pixels):
                pixels[idx] = (255, 215, 0)

    # 添加一个大圆点表示装饰
    center_x, center_y = 128, 180
    radius = 20
    for y in range(max(0, center_y - radius), min(height, center_y + radius)):
        for x in range(max(0, center_x - radius), min(width, center_x + radius)):
            dx = x - center_x
            dy = y - center_y
            if dx * dx + dy * dy <= radius * radius:
                idx = y * width + x
                if idx < len(pixels):
                    pixels[idx] = (255, 255, 255)  # 白色圆点

    return create_png(width, height, pixels)

def main():
    output_dir = os.path.join(os.path.dirname(__file__), "images")
    os.makedirs(output_dir, exist_ok=True)

    print("正在生成欢迎图片...\n")

    # 生成欢迎图片
    welcome_png = create_welcome_simple()
    output_path = os.path.join(output_dir, "welcome.png")

    with open(output_path, 'wb') as f:
        f.write(welcome_png)

    print(f"[OK] welcome.png (256x256)")
    print(f"  保存到: {output_path}")

if __name__ == "__main__":
    main()
