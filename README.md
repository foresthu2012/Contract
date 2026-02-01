# Niucloud 合同签署插件 (Contract Addon) 开发文档

## 1. 项目简介
本插件是基于 Niucloud-Admin 框架开发的合同签署功能模块，支持后台管理合同、会员在线预览及手写签名。严格遵循 Niucloud V6 插件开发规范。

## 2. 目录结构
插件根目录：`addon/Contract/`

```text
addon/Contract/
├── Addon.php                   # 插件安装/卸载/更新入口文件
├── info.json                   # 插件基础信息配置
├── package/
│   └── uni-app-pages.php       # Uni-app 页面路由配置（编译时合并）
├── app/
│   ├── model/                  # 数据模型
│   │   └── Contract.php        # 合同模型 (自动处理时间戳格式化)
│   ├── service/                # 业务逻辑层
│   │   ├── admin/contract/ContractService.php  # 后台业务逻辑
│   │   └── api/contract/ContractService.php    # 前端 API 业务逻辑
│   ├── validate/               # 验证器
│   │   └── contract/Contract.php
│   ├── adminapi/               # 后台 API 接口 (Controller)
│   │   ├── controller/contract/Contract.php
│   │   └── route/route.php     # 后台路由配置
│   ├── api/                    # 前端 API 接口 (Controller)
│   │   ├── controller/contract/Contract.php
│   │   └── route/route.php     # 前端路由配置
│   ├── dict/                   # 字典配置
│   │   ├── menu/               # 菜单配置
│   │   │   ├── admin.php       # 后台菜单
│   │   │   └── member.php      # 会员中心菜单
│   │   └── diy/
│   │       └── links.php       # 装修链接配置 (我的合同)
│   └── lang/                   # 多语言包
├── admin/                      # 后台前端 (Vue3 + Element Plus)
│   ├── api/                    # 后台 API 请求定义
│   └── views/contract/         # 页面视图
│       └── contract/
│           ├── index.vue       # 合同列表
│           ├── edit.vue        # 添加/编辑弹窗 (含富文本/文件上传)
│           ├── member_select_popup.vue # 会员选择组件
│           └── order_select_popup.vue  # 订单选择组件
├── uni-app/                    # 移动端前端 (Uni-app)
│   ├── api/                    # 移动端 API 请求定义
│   └── pages/contract/
│       ├── list.vue            # 合同列表 (支持下拉刷新/Tab切换)
│       └── detail.vue          # 合同详情 (含手写签名/文件预览)
└── sql/                        # 数据库脚本
    ├── install.sql             # 安装 SQL
    ├── uninstall.sql           # 卸载 SQL
    └── update_1.0.1.sql        # 更新 SQL
```

## 3. 开发环境准备
1.  **Niucloud 框架**: 确保已安装 Niucloud-Admin V6+ 版本。
2.  **Node.js**: 用于编译前端代码。
3.  **HBuilderX**: 用于运行和调试 Uni-app 移动端。
4.  **PHP 8.0+**: 后端运行环境。

## 4. 核心功能实现

### 4.1 数据库设计
表名：`addon_contract_contract`
*   `id`: 主键
*   `site_id`: 站点ID
*   `title`: 合同标题
*   `member_id`: 关联会员ID
*   `order_id`: 关联订单ID (可选)
*   `content`: 合同内容 (富文本)
*   `file_path`: 合同文件路径 (PDF/图片)
*   `sign_image`: 签名图片路径
*   `status`: 状态 (0:待签署, 1:已签署)
*   `create_time`, `sign_time`, `update_time`, `delete_time`: 时间戳

### 4.2 后台管理 (Admin)
*   **列表页**: 使用 `useTable` 钩子实现分页查询、搜索。
*   **添加/编辑**:
    *   **会员选择**: 封装了 `member-select-popup`，复用系统会员接口。
    *   **订单关联**: 选择会员后，自动筛选该会员名下的订单。
    *   **文件上传**: 移除了文件类型限制，支持 PDF/Word/图片。
    *   **校验逻辑**: `edit.vue` 中实现了“合同内容”与“合同文件”二选一校验。

### 4.3 移动端 (Uni-app)
*   **列表页 (`list.vue`)**:
    *   启用下拉刷新 (`enablePullDownRefresh: true`)。
    *   适配底部安全区 (`safe-area-inset-bottom`)。
    *   卡片式 UI 设计。
*   **详情页 (`detail.vue`)**:
    *   **文件预览**: 使用 `img()` 处理路径，支持 `uni.previewImage` (图片) 和 `uni.openDocument` (文档)。
    *   **手写签名**: 使用 Canvas 实现。**关键点**: 添加 `touch-action: none` 解决 H5 端滚动冲突。
    *   **签名上传**: 使用 `uni.uploadFile` 上传签名图片到 `/api/file/image` 接口。

## 5. 部署与发布流程

### 5.1 本地开发
1.  **后端**: 修改 PHP 代码后，执行 `php think clear` 清理缓存。
2.  **Admin 前端**: 修改 `admin/` 下代码后，浏览器自动热更新。
3.  **Uni-app 前端**:
    *   修改 `uni-app/` 下代码。
    *   修改 `package/uni-app-pages.php` (路由配置) 后，必须**重启编译命令** (`npm run dev:mp-weixin` 或 HBuilderX 重新运行)。

### 5.2 线上部署 (宝塔/服务器)
1.  **上传代码**: 将 `addon/Contract` 目录完整上传到服务器对应目录。
2.  **清理缓存**:
    *   后端: 删除 `runtime/` 目录或执行 `php think clear`。
3.  **前端编译 (必须)**:
    *   **H5/小程序**: 在后台点击 **“云编译”**，或者在本地执行 `npm run build:mp-weixin` 后上传代码包到微信后台。
    *   **注意**: 仅上传 PHP 文件不会更新小程序页面，必须重新编译前端包。

## 6. 常见问题排查
*   **Q: 点击“我的合同”无反应？**
    *   A: 路由未注册。请重启前端编译，确保 `pages.json` 中已包含 `addon/Contract/pages/contract/list`。
*   **Q: 签名签不上/画不出线？**
    *   A: 检查 `detail.vue` 样式中是否包含 `touch-action: none`。
*   **Q: 报错 "only array cache can be push"？**
    *   A: 后端缓存冲突。请务必执行 `php think clear`。
*   **Q: 文件预览失败？**
    *   A: 检查文件路径是否为绝对路径。已封装 `img()` 函数自动处理。

## 7. API 接口文档
*   `GET addon/Contract/api/contract/contract`: 获取合同列表
*   `GET addon/Contract/api/contract/contract/:id`: 获取合同详情
*   `POST addon/Contract/api/contract/contract/sign/:id`: 提交签名
    *   参数: `sign_image` (图片路径)
