# MEBImgScreen - 方块像素画广告屏插件

一个 PocketMine-MP 5.x 插件，使用彩色方块自动构建像素画广告屏，将图片转换为游戏内的方块艺术。

## ✨ 特性

- ✅ 支持静态图片（PNG/JPG）
- ✅ 使用彩色混凝土、羊毛等方块构建像素画
- ✅ **一键生成**：站在原地输入命令，自动放置方块
- ✅ 智能颜色匹配，使用 50+ 种方块类型
- ✅ 自由尺寸，每个方块 = 1 像素（推荐 16×16 到 64×64）
- ✅ 支持四个方向（东南西北）
- ✅ 支持多个广告位同时运行

## 📦 安装

1. 将插件放入 `plugins` 目录
2. 重启服务器
3. 将图片文件放入 `plugins/MEBAdScreen/images/` 目录

## 🚀 快速开始

### 最简单的方式（推荐）

```bash
# 1. 将图片放入 plugins/MEBAdScreen/images/
# 2. 站在你想要的位置，面向屏幕应该朝向的方向
# 3. 输入命令

/imgscreen make 你的图片.png

# 就这样！插件会自动创建 16×16 的方块像素画
```

### 指定尺寸

```bash
# 创建 32×32 的大屏
/imgscreen make banner.png 32 32

# 创建 48×24 的横幅
/imgscreen make wide.png 48 24
```

## 📖 工作原理

插件会将图片转换为方块像素画：
1. 读取图片并调整到指定尺寸（宽×高）
2. 分析每个像素的 RGB 颜色
3. 匹配最接近的 Minecraft 方块（混凝土、羊毛、矿石等）
4. 在游戏世界中自动放置对应方块

每个方块 = 图片的 1 个像素，推荐尺寸 16×16 到 64×64 方块。

## 🎮 命令列表

| 命令 | 说明 | 示例 |
|------|------|------|
| `/imgscreen make <图片名> [宽] [高]` | 快速创建（默认16×16） | `/imgscreen make logo.png 32 32` |
| `/imgscreen create <ID> <宽> <高> <方向> <图片名>` | 完整创建 | `/imgscreen create ad1 48 48 north banner.png` |
| `/imgscreen list` | 列出所有广告屏 | - |
| `/imgscreen info <ID>` | 查看详细信息 | `/imgscreen info ad1` |
| `/imgscreen remove <ID>` | 删除广告屏 | `/imgscreen remove ad1` |
| `/imgscreen reload` | 重载配置 | - |

## ⚙️ 配置文件

`plugins/MEBAdScreen/config.yml`:

```yaml
版本: 1.0.0
最大屏幕数: 50
```

## 🎨 尺寸参考

| 尺寸 | 方块数量 | 适用场景 |
|------|----------|---------|
| 16×16 | 256 方块 | 小标识 |
| 32×32 | 1024 方块 | 标准广告 |
| 48×48 | 2304 方块 | 大型展示 |
| 64×64 | 4096 方块 | 超大屏幕 |

**注意**：尺寸越大，放置方块耗时越长，建议不超过 64×64。

## 🎯 使用示例

### 服务器大厅欢迎屏
```bash
/imgscreen make welcome.png 48 48
```

### 商店广告牌
```bash
/imgscreen make shop_ad.png 32 24
```

### 服务器 Logo
```bash
/imgscreen make logo.png 64 64
```

## ⚡ 图片优化建议

**图片准备：**
- 调整到目标尺寸（16×16 到 64×64 像素）
- 使用简单明快的颜色和清晰的图案
- 压缩文件大小（推荐 < 1MB）
- 使用 [TinyPNG](https://tinypng.com/) 压缩

**颜色说明：**
- 插件使用 50+ 种 Minecraft 方块
- 包括：彩色混凝土、羊毛、矿石块、石头等
- 自动匹配最接近的颜色

## 📋 技术说明

- 使用 PocketMine-MP 5.0.0 API
- 基于 GD 库处理图片
- 智能颜色匹配算法（欧几里得距离）
- 方块调色板：16 种染料色 × 2 种方块 + 13 种特殊方块

## ❓ 常见问题

**Q: 支持 GIF 动画吗？**  
A: 当前版本不支持，仅支持静态图片（PNG/JPG）。

**Q: 为什么我的图片颜色不准确？**  
A: Minecraft 方块的颜色有限，插件会自动匹配最接近的颜色。建议使用饱和度高、对比度强的图片。

**Q: 可以创建多大的屏幕？**  
A: 理论上可以创建 1×1 到 128×128 的屏幕，但推荐 16×16 到 64×64，太大会导致放置时间过长。

**Q: 可以删除已创建的屏幕吗？**  
A: 可以，使用 `/imgscreen remove <ID>` 命令会自动清除所有方块。

## 📄 许可

MIT License

## 👤 作者

**MengBao** - [@MengBaofh](https://github.com/MengBaofh)
