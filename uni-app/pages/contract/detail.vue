<template>
    <view class="container">
        <!-- Contract Header -->
        <view class="header-section">
            <view class="title">{{ detail.title }}</view>
            <view class="status-badge" :class="detail.status == 1 ? 'signed' : 'pending'">
                {{ detail.status == 1 ? '已签署' : '待签署' }}
            </view>
        </view>

        <!-- Document Preview Link -->
        <view class="section-card" v-if="detail.file_path">
            <view class="section-title">合同文件</view>
            <view class="file-preview-btn" @click="openFile(detail.file_path)">
                <text class="icon">📄</text>
                <text class="text">预览合同原件 (PDF/图片)</text>
                <text class="arrow">></text>
            </view>
        </view>

        <!-- Rich Text Content -->
        <view class="section-card" v-if="detail.content">
            <view class="section-title">合同条款</view>
            <view class="rich-content">
                <rich-text :nodes="detail.content"></rich-text>
            </view>
        </view>

        <!-- Signature Area (Pending) -->
        <view class="section-card sign-section" v-if="detail.status == 0">
            <view class="section-title">手写签名 <text class="tip">(请在下方灰色区域书写)</text></view>
            <view class="canvas-wrapper">
                <canvas class="sign-canvas" canvas-id="signCanvas" @touchstart="touchstart" @touchmove="touchmove" @touchend="touchend" disable-scroll="true"></canvas>
                <view class="canvas-placeholder" v-if="!isSigned">请在此处签名</view>
            </view>
            <view class="action-btns">
                <button class="btn-reset" @click="clearSign">清除重写</button>
                <button class="btn-submit" type="primary" @click="submitSign">确认签署</button>
            </view>
        </view>

        <!-- Signed Info -->
        <view class="section-card signed-result" v-if="detail.status == 1">
            <view class="section-title">签署信息</view>
            <view class="signature-display">
                <text>您的签名：</text>
                <image :src="detail.sign_image" mode="aspectFit" class="sign-img"></image>
            </view>
            <view class="sign-meta">
                <text>签署时间：{{ detail.sign_time }}</text>
            </view>
            <!-- Watermark -->
            <view class="watermark">已签署</view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, getCurrentInstance } from 'vue'
import { onLoad } from '@dcloudio/uni-app'
import { getContractInfo, signContract } from '@/addon/Contract/api/contract'
import { img } from '@/utils/common'

const detail = ref({})
const contractId = ref(0)
const ctx = ref(null)

const isSigned = ref(false)

onLoad((options) => {
    if (options.id) {
        contractId.value = options.id
        getDetail()
    }
})

const getDetail = async () => {
    const res = await getContractInfo(contractId.value)
    detail.value = res.data
    if (detail.value.status == 0) {
        initCanvas()
    }
}

const openFile = (path) => {
    if (!path) return
    
    // 使用系统工具处理完整路径
    const fullPath = img(path)
    
    // 检查文件类型
    const fileType = path.substring(path.lastIndexOf('.') + 1).toLowerCase()
    
    // 图片类型直接预览，支持手势缩放
    if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(fileType)) {
        uni.previewImage({
            urls: [fullPath],
            current: 0,
            indicator: 'default',
            loop: false
        })
    } else {
        // 文档类型（PDF/Doc等）下载后打开
        uni.showLoading({ title: '打开中...' })
        
        uni.downloadFile({
            url: fullPath,
            success: (res) => {
                if (res.statusCode === 200) {
                    uni.openDocument({
                        filePath: res.tempFilePath,
                        showMenu: true,
                        success: function () {
                            console.log('打开文档成功')
                        },
                        fail: function(err) {
                            console.error(err)
                            uni.showToast({ title: '无法打开此格式文件', icon: 'none' })
                        }
                    })
                } else {
                    uni.showToast({ title: '下载失败: ' + res.statusCode, icon: 'none' })
                }
            },
            fail: (err) => {
                console.error(err)
                uni.showToast({ title: '文件下载请求失败', icon: 'none' })
            },
            complete: () => {
                uni.hideLoading()
            }
        })
    }
}

const initCanvas = () => {
    setTimeout(() => {
        ctx.value = uni.createCanvasContext('signCanvas')
        ctx.value.setLineWidth(4)
        ctx.value.setLineCap('round')
        ctx.value.setStrokeStyle('#000000')
    }, 500)
}

const touchstart = (e) => {
    let startX = e.touches[0].x
    let startY = e.touches[0].y
    ctx.value.beginPath()
    ctx.value.moveTo(startX, startY)
}

const touchmove = (e) => {
    let moveX = e.touches[0].x
    let moveY = e.touches[0].y
    ctx.value.lineTo(moveX, moveY)
    ctx.value.stroke()
    ctx.value.draw(true)
    isSigned.value = true
}

const touchend = () => {
}

const clearSign = () => {
    ctx.value.clearRect(0, 0, 1000, 1000)
    ctx.value.draw()
    isSigned.value = false
}

const submitSign = () => {
    if (!isSigned.value) {
        uni.showToast({ title: '请先签名', icon: 'none' })
        return
    }
    uni.showModal({
        title: '提示',
        content: '确认提交签名吗？提交后不可修改。',
        success: (res) => {
            if (res.confirm) {
                uni.canvasToTempFilePath({
                    canvasId: 'signCanvas',
                    success: (res) => {
                         uploadSignature(res.tempFilePath)
                    }
                })
            }
        }
    })
}

const uploadSignature = (filePath) => {
    uni.showLoading({ title: '上传签名中...' })
    
    // 获取基础URL，通常存储在本地或配置文件中
    // 注意：这里假设了上传接口地址，实际请根据项目情况调整
    const baseUrl = uni.getStorageSync('baseUrl') || ''
    const uploadUrl = baseUrl + '/api/file/image'
    
    uni.uploadFile({
        url: uploadUrl, 
        filePath: filePath,
        name: 'file',
        header: {
            token: uni.getStorageSync('token')
        },
        success: (uploadFileRes) => {
            uni.hideLoading()
            let data
            try {
                data = JSON.parse(uploadFileRes.data)
            } catch (e) {
                uni.showToast({ title: '上传失败，响应解析错误', icon: 'none' })
                return
            }
            
            if (data.code == 1) {
                const imagePath = data.data.url
                signContract(contractId.value, { sign_image: imagePath }).then(res => {
                    uni.showToast({ title: '签署成功' })
                    getDetail()
                })
            } else {
                uni.showToast({ title: data.msg || '上传失败', icon: 'none' })
            }
        },
        fail: (err) => {
            uni.hideLoading()
            uni.showToast({ title: '上传请求失败', icon: 'none' })
            console.error(err)
        }
    })
}
</script>

<style lang="scss" scoped>
.container {
    padding: 30rpx;
    padding-bottom: calc(30rpx + env(safe-area-inset-bottom));
    background-color: #f8f8f8;
    min-height: 100vh;
}
.header-section {
    background: #fff;
    padding: 30rpx;
    border-radius: 16rpx;
    margin-bottom: 20rpx;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.03);
    .title {
        font-size: 36rpx;
        font-weight: bold;
        color: #333;
    }
    .status-badge {
        font-size: 26rpx;
        padding: 8rpx 20rpx;
        border-radius: 30rpx;
        &.pending {
            background-color: #e6f7ff;
            color: #1890ff;
        }
        &.signed {
            background-color: #f6ffed;
            color: #52c41a;
        }
    }
}
.section-card {
    background: #fff;
    padding: 30rpx;
    border-radius: 16rpx;
    margin-bottom: 20rpx;
    box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.03);
    position: relative;
    .section-title {
        font-size: 30rpx;
        font-weight: bold;
        margin-bottom: 20rpx;
        color: #333;
        border-left: 6rpx solid #007aff;
        padding-left: 16rpx;
        line-height: 1;
        .tip {
            font-size: 24rpx;
            color: #999;
            font-weight: normal;
            margin-left: 10rpx;
        }
    }
}
.file-preview-btn {
    display: flex;
    align-items: center;
    background-color: #f5f7fa;
    padding: 24rpx;
    border-radius: 12rpx;
    .icon {
        font-size: 40rpx;
        margin-right: 20rpx;
    }
    .text {
        flex: 1;
        font-size: 28rpx;
        color: #333;
    }
    .arrow {
        color: #ccc;
    }
    &:active {
        background-color: #eee;
    }
}
.rich-content {
    font-size: 28rpx;
    color: #666;
    line-height: 1.6;
}
.canvas-wrapper {
    position: relative;
    width: 100%;
    height: 400rpx;
    margin-bottom: 30rpx;
    .sign-canvas {
        width: 100%;
        height: 100%;
        background-color: #f5f5f5;
        border-radius: 12rpx;
        border: 2rpx dashed #ddd;
        /* #ifdef H5 */
        touch-action: none;
        /* #endif */
    }
    .canvas-placeholder {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: #ccc;
        font-size: 32rpx;
        pointer-events: none;
    }
}
.action-btns {
    display: flex;
    justify-content: space-between;
    gap: 30rpx;
    button {
        flex: 1;
        font-size: 30rpx;
        height: 88rpx;
        line-height: 88rpx;
        border-radius: 44rpx;
        &.btn-reset {
            background-color: #f5f5f5;
            color: #666;
            &::after { border: none; }
        }
        &.btn-submit {
            background-color: #007aff;
            color: #fff;
        }
    }
}
.signature-display {
    text-align: center;
    padding: 20rpx 0;
    .sign-img {
        width: 100%;
        height: 200rpx;
        margin-top: 20rpx;
    }
}
.sign-meta {
    text-align: right;
    font-size: 24rpx;
    color: #999;
    margin-top: 20rpx;
    border-top: 1rpx solid #eee;
    padding-top: 20rpx;
}
.watermark {
    position: absolute;
    top: 40rpx;
    right: 40rpx;
    font-size: 100rpx;
    color: rgba(82, 196, 26, 0.15);
    font-weight: bold;
    transform: rotate(-30deg);
    border: 6rpx solid rgba(82, 196, 26, 0.15);
    padding: 10rpx 40rpx;
    border-radius: 20rpx;
    pointer-events: none;
}
</style>
