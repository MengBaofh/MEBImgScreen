# MEBAdScreen - 广告屏插件

一个高性能的 PocketMine-MP 5.x 广告屏插件，使用地图渲染技术展示图片和 GIF 动画。

## ✨ 特性

- ✅ 支持静态图片（PNG/JPG）和 GIF 动画（需要 Imagick）
- ✅ 可拼接多个地图形成大屏（最大 4×4 = 512×512 像素）
- ✅ **一键生成**：站在原地输入命令，自动创建屏幕
- ✅ 智能距离检测，只向附近玩家发送数据
- ✅ 性能优化模式，玩家多时自动降低帧率
- ✅ 完整的 Minecraft 颜色表映射（240+ 种颜色）
- ✅ 自动构建物品展示框和背景墙
- ✅ 支持多个广告位同时运行

## 📦 安装

1. 将插件放入 `plugins` 目录
2. 重启服务器
3. 将图片/GIF 文件放入 `plugins/MEBAdScreen/images/` 目录

## 🚀 快速开始

### 最简单的方式（推荐）

```bash
# 1. 将图片放入 plugins/MEBAdScreen/images/
# 2. 站在你想要的位置，面向屏幕应该朝向的方向
# 3. 输入命令

/adscreen make 你的图片.png

# 就这样！插件会自动创建 2×2 的广告屏
```

### 指定尺寸

```bash
# 创建 3×3 的大屏
/adscreen make banner.gif 3 3

# 创建 3×2 的横幅
/adscreen make wide.png 3 2
```

## 📖 详细教程

完整使用教程请查看 [TUTORIAL.md](TUTORIAL.md)

## 🎮 命令列表

| 命令 | 说明 | 示例 |
|------|------|------|
| `/adscreen make <图片> [宽] [高]` | 快速创建（推荐） | `/adscreen make logo.png 2 2` |
| `/adscreen create <ID> <宽> <高> <方向> <图片>` | 完整创建 | `/adscreen create ad1 3 3 north banner.gif` |
| `/adscreen list` | 列出所有广告屏 | - |
| `/adscreen info <ID>` | 查看详细信息 | `/adscreen info ad1` |
| `/adscreen remove <ID>` | 删除广告屏 | `/adscreen remove ad1` |
| `/adscreen reload` | 重载配置 | - |

## ⚙️ 配置文件

`plugins/MEBAdScreen/config.yml`:

```yaml
版本: 1.0.0
最大可视距离: 64
距离检测间隔(s): 1.0
GIF帧间隔(tick): 4
异步渲染: true
最大屏幕数: 50
地图起始ID: 1000

性能模式:
  启用: true
  玩家少于N人时全速: 5
  玩家多时降低帧率: true
```

## 🎨 尺寸参考

| 大小 | 分辨率 | 适用场景 |
|------|--------|---------|
| 1×1 | 128×128px | 小图标 |
| 2×2 | 256×256px | 标准广告 |
| 3×3 | 384×384px | 大型宣传 |
| 4×4 | 512×512px | 超大屏幕 |

## 🔧 生成测试图片

```bash
cd plugins/MEBAdScreen
php example_images.php
```

会生成多张测试图片，可直接使用。

## 🎯 使用示例

### 服务器大厅欢迎屏
```bash
/adscreen make welcome.png 4 4
```

### 动态广告牌
```bash
/adscreen make promotion.gif 3 2
```

## ⚡ 性能优化

**图片优化建议：**
- 调整到合适尺寸（256/384/512px）
- 压缩 GIF（15-30帧，2MB以内）
- 使用 [TinyPNG](https://tinypng.com/) 或 [ezgif](https://ezgif.com/)

**服务器配置建议：**
- 小型（<10人）：可视距离 64，帧间隔 4
- 中型（10-30人）：可视距离 48，帧间隔 6  
- 大型（>30人）：可视距离 32，帧间隔 8

## 📋 技术说明

- 使用完整 MC 颜色表（248种颜色）
- Imagick 支持多帧 GIF，GD 仅第一帧
- 智能距离检测和按需发送

## 📄 许可

MIT License

## 👤 作者

**MengBao** - [@MengBaofh](https://github.com/MengBaofh)

---

查看 [TUTORIAL.md](TUTORIAL.md) 获取详细教程
