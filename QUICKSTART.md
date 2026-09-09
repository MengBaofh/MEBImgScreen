# 🚀 快速开始

## 30秒上手

### 1️⃣ 安装插件
```bash
# 将 MEBAdScreen 文件夹放入 plugins 目录
cp -r MEBAdScreen /path/to/pocketmine/plugins/

# 重启服务器
```

### 2️⃣ 生成测试图片
```bash
cd plugins/MEBAdScreen
php example_images.php
```

**会生成：**
- `welcome.png` - 欢迎屏（256×256）
- `rules.png` - 规则板（384×256）
- `shop_sale.png` - 促销广告（256×256）
- `colortest.png` - 颜色测试（512×512）
- `logo.png` - Logo示例（128×128）

### 3️⃣ 创建第一个广告屏

进入游戏，站在你想放置屏幕的位置：

```bash
/adscreen make welcome.png
```

✅ 完成！一个 2×2 的欢迎屏已经出现在你面前了！

---

## 更多尺寸

```bash
# 小图标
/adscreen make logo.png 1 1

# 标准广告
/adscreen make shop_sale.png 2 2

# 大型海报
/adscreen make rules.png 3 2

# 超大屏幕
/adscreen make colortest.png 4 4
```

## 管理你的屏幕

```bash
# 查看所有屏幕
/adscreen list

# 查看详细信息
/adscreen info <ID>

# 删除屏幕
/adscreen remove <ID>
```

## 下一步

- 📖 阅读 [完整教程](TUTORIAL.md)
- 🎨 学习如何优化图片
- ⚙️ 调整性能配置

---

**问题？** 查看 [README.md](README.md) 或 [TUTORIAL.md](TUTORIAL.md)
