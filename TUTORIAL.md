# MEBAdScreen 使用教程

## 快速开始（推荐新手）

### 1. 准备图片
将你的图片（PNG/JPG/GIF）放入：
```
plugins/MEBAdScreen/images/
```

**图片建议：**
- 分辨率：256×256（2×2屏）、384×384（3×3屏）或 512×512（4×4屏）
- 格式：PNG、JPG 或 GIF
- GIF 建议 15-30 帧，文件小于 2MB

### 2. 一键创建广告屏

站在你想放置广告屏的位置，面向你希望屏幕朝向的方向，输入：

```bash
/adscreen make 你的图片.png
```

**示例：**
```bash
# 创建默认 2×2 的广告屏
/adscreen make logo.png

# 创建 3×2 的广告屏（宽3格，高2格）
/adscreen make banner.gif 3 2

# 创建 4×4 的超大屏
/adscreen make poster.jpg 4 4
```

就这么简单！插件会自动：
- ✅ 在你面前 3 格处生成屏幕
- ✅ 根据你的朝向调整屏幕方向
- ✅ 放置背景墙和物品展示框
- ✅ 加载并渲染图片
- ✅ 如果是 GIF，自动循环播放

## 高级用法

### 完整创建命令

如果你需要精确控制，使用完整命令：

```bash
/adscreen create <ID> <宽> <高> <方向> <图片名>
```

**参数说明：**
- `ID`：广告屏唯一标识（用于管理）
- `宽`：1-4，表示横向地图数
- `高`：1-4，表示纵向地图数
- `方向`：north（北）、south（南）、east（东）、west（西）
- `图片名`：images 文件夹中的文件名

**示例：**
```bash
/adscreen create spawn_ad 3 3 north welcome.gif
```

### 管理命令

```bash
# 列出所有广告屏
/adscreen list

# 查看详细信息
/adscreen info spawn_ad

# 删除广告屏
/adscreen remove spawn_ad

# 重载配置
/adscreen reload
```

## 尺寸对照表

| 大小 | 实际分辨率 | 适用场景 |
|------|-----------|---------|
| 1×1 | 128×128px | 小图标、路标 |
| 2×2 | 256×256px | 标准广告、Logo |
| 3×3 | 384×384px | 大型宣传画 |
| 4×4 | 512×512px | 超大屏幕、背景墙 |
| 3×2 | 384×256px | 横幅广告 |
| 2×3 | 256×384px | 竖版海报 |

## 实战案例

### 案例1：服务器大厅欢迎屏

```bash
# 准备一张 512×512 的欢迎图
/adscreen make welcome.png 4 4
```

### 案例2：商店广告牌

```bash
# 创建 3×2 横向广告
/adscreen make shop_sale.gif 3 2
```

### 案例3：规则展示板

```bash
# 多个 2×2 屏幕排列
/adscreen make rule1.png 2 2
# 移动 2 格，继续创建
/adscreen make rule2.png 2 2
```

### 案例4：动态背景墙

```bash
# 使用 GIF 作为动态装饰
/adscreen make particles.gif 4 3
```

## 性能优化建议

### 图片优化

1. **提前调整尺寸**
   ```bash
   # 使用工具将图片调整到目标尺寸
   # 2×2 屏 → 256×256
   # 3×3 屏 → 384×384
   ```

2. **压缩 GIF**
   - 使用在线工具（如 ezgif.com）
   - 减少帧数到 20-30 帧
   - 降低颜色数到 128 色

3. **推荐工具**
   - [TinyPNG](https://tinypng.com/) - 压缩 PNG/JPG
   - [ezgif](https://ezgif.com/) - 优化 GIF
   - Photoshop/GIMP - 专业编辑

### 服务器配置

编辑 `plugins/MEBAdScreen/config.yml`：

```yaml
# 玩家少时（5人以下）全速播放
性能模式:
  启用: true
  玩家少于N人时全速: 5
  玩家多时降低帧率: true

# 根据服务器性能调整
最大可视距离: 64        # 减小到 32 可提升性能
GIF帧间隔(tick): 4      # 增大到 6-8 可降低负载
```

### 使用建议

- ✅ 大厅/出生点：2-3 个广告屏
- ✅ 商店区域：4-6 个广告屏
- ⚠️ 避免在同一视野内放置超过 5 个动画屏
- ⚠️ 单个服务器建议不超过 20 个广告屏

## 常见问题

### Q: 屏幕显示不出来？
**A:** 检查：
1. 图片是否在 `images/` 目录
2. 图片文件名是否正确（区分大小写）
3. 使用 `/adscreen list` 确认是否创建成功

### Q: GIF 不动？
**A:** 可能原因：
1. 服务器未安装 Imagick 扩展（会降级为静态第一帧）
2. GIF 只有一帧
3. 性能模式降低了帧率

### Q: 颜色不准确？
**A:** 这是正常的。Minecraft 地图只有约 240 种颜色，复杂图片会有色差。建议：
- 使用高对比度图片
- 避免渐变色
- 测试后调整原图

### Q: 如何移动广告屏？
**A:** 目前需要删除后重建：
```bash
/adscreen remove old_ad
# 移动到新位置
/adscreen make image.png 2 2
```

### Q: 可以旋转屏幕吗？
**A:** 目前仅支持 4 个基本方向（north/south/east/west），不支持 45° 等角度。

## 技术说明

### 工作原理

1. **图像处理**：GD/Imagick 将图片转为 Minecraft 地图颜色
2. **分块渲染**：大图分成 128×128 的块
3. **智能推送**：只向附近玩家发送数据
4. **帧动画**：预渲染所有帧，定时切换

### 依赖检测

```bash
# 在服务器控制台查看日志
[MEBAdScreen] Imagick: 已安装 ✓  (支持多帧 GIF)
[MEBAdScreen] GD: 已安装 ✓
```

如果只有 GD，GIF 会显示为静态第一帧。

### 地图 ID 分配

- 起始 ID：1000（可在配置中修改）
- 每个屏幕占用 `宽×高` 个 ID
- 示例：2×2 屏占用 ID 1000-1003

## 高级技巧

### 1. 广告轮播

手动实现轮播效果：

```bash
# 创建多个屏幕在同一位置
/adscreen create ad1 3 3 north banner1.png
# 等待 30 秒
/adscreen remove ad1
/adscreen create ad2 3 3 north banner2.png
```

### 2. 拼接屏幕墙

创建 2×2 的屏幕阵列（总共 8×8 格）：

```bash
# 第一排
/adscreen make part1.png 4 4
# 向右移动 4 格
/adscreen make part2.png 4 4
# 向下移动 4 格，向左移动 4 格
/adscreen make part3.png 4 4
# 向右移动 4 格
/adscreen make part4.png 4 4
```

### 3. 动态信息屏

配合外部工具生成实时图片：

```python
# Python 示例：生成服务器信息图
from PIL import Image, ImageDraw, ImageFont

def generate_info():
    img = Image.new('RGB', (256, 256), 'black')
    draw = ImageDraw.Draw(img)
    draw.text((10, 10), "在线: 42人", fill='white')
    img.save('plugins/MEBAdScreen/images/info.png')

# 定时运行后执行 /adscreen reload
```

## 许可与支持

- **开源协议**：MIT License
- **作者**：MengBao
- **反馈**：GitHub Issues

---

**祝你使用愉快！如有问题欢迎反馈。**
