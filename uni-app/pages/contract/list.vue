<template>
    <view class="container">
        <view class="tabs">
            <view class="tab-item" :class="{ active: status === 0 }" @click="changeTab(0)">
                <text>待签署</text>
                <view class="line" v-if="status === 0"></view>
            </view>
            <view class="tab-item" :class="{ active: status === 1 }" @click="changeTab(1)">
                <text>已签署</text>
                <view class="line" v-if="status === 1"></view>
            </view>
        </view>

        <view class="list-container">
            <view class="card-list" v-if="list.length > 0">
                <view class="card-item" v-for="(item, index) in list" :key="index" @click="toDetail(item.id)">
                    <view class="card-header">
                        <text class="card-title">{{ item.title }}</text>
                        <view class="status-tag" :class="item.status == 0 ? 'tag-pending' : 'tag-success'">
                            {{ item.status == 0 ? '待签署' : '已签署' }}
                        </view>
                    </view>
                    <view class="card-body">
                        <view class="info-row" v-if="item.order_id">
                            <text class="label">关联订单</text>
                            <text class="value">{{ item.order_id }}</text>
                        </view>
                        <view class="info-row">
                            <text class="label">创建时间</text>
                            <text class="value">{{ item.create_time }}</text>
                        </view>
                        <view class="info-row" v-if="item.status == 1 && item.sign_time">
                            <text class="label">签署时间</text>
                            <text class="value">{{ item.sign_time }}</text>
                        </view>
                    </view>
                    <view class="card-footer">
                        <text class="btn-text">查看详情 ></text>
                    </view>
                </view>
                <uni-load-more :status="loadingStatus"></uni-load-more>
            </view>
            <view v-else class="empty">
                <text>暂无合同数据</text>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getContractList } from '@/addon/Contract/api/contract'
import { onShow, onPullDownRefresh, onReachBottom } from '@dcloudio/uni-app'

const list = ref([])
const status = ref(0)
const page = ref(1)
const loadingStatus = ref('more')
const isLoaded = ref(false)

const getList = async (mode = 'refresh') => {
    if (mode === 'refresh') {
        page.value = 1
        list.value = []
        loadingStatus.value = 'loading'
    } else {
        if (loadingStatus.value === 'noMore') return
        page.value++
    }

    try {
        const res = await getContractList({ 
            page: page.value, 
            limit: 10,
            status: status.value 
        })
        if (mode === 'refresh') {
            list.value = res.data.data
            uni.stopPullDownRefresh()
        } else {
            list.value = [...list.value, ...res.data.data]
        }

        if (list.value.length >= res.data.total) {
            loadingStatus.value = 'noMore'
        } else {
            loadingStatus.value = 'more'
        }
        isLoaded.value = true
    } catch (e) {
        loadingStatus.value = 'more'
        uni.stopPullDownRefresh()
    }
}

const changeTab = (val) => {
    status.value = val
    getList('refresh')
}

onShow(() => {
    getList('refresh')
})

onPullDownRefresh(() => {
    getList('refresh')
})

onReachBottom(() => {
    getList('loadmore')
})

const toDetail = (id) => {
    uni.navigateTo({
        url: `/addon/Contract/pages/contract/detail?id=${id}`
    })
}
</script>

<style lang="scss" scoped>
.container {
    background-color: #f8f8f8;
    min-height: 100vh;
}
.tabs {
    display: flex;
    background: #fff;
    padding: 0 30rpx;
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2rpx 10rpx rgba(0,0,0,0.05);
    .tab-item {
        flex: 1;
        text-align: center;
        font-size: 30rpx;
        position: relative;
        height: 88rpx;
        line-height: 88rpx;
        color: #666;
        &.active {
            color: #333;
            font-weight: bold;
            font-size: 32rpx;
        }
        .line {
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 40rpx;
            height: 6rpx;
            background-color: #007aff;
            border-radius: 3rpx;
        }
    }
}
.list-container {
    padding: 20rpx 30rpx;
    padding-bottom: calc(20rpx + env(safe-area-inset-bottom));
}
.card-item {
    background: #fff;
    padding: 30rpx;
    border-radius: 16rpx;
    margin-bottom: 20rpx;
    box-shadow: 0 2rpx 12rpx rgba(0,0,0,0.03);
    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20rpx;
        padding-bottom: 20rpx;
        border-bottom: 1rpx solid #f0f0f0;
        .card-title {
            font-size: 32rpx;
            font-weight: bold;
            flex: 1;
            margin-right: 20rpx;
            color: #333;
        }
        .status-tag {
            font-size: 24rpx;
            padding: 6rpx 16rpx;
            border-radius: 8rpx;
            &.tag-pending {
                background-color: #e6f7ff;
                color: #1890ff;
            }
            &.tag-success {
                background-color: #f6ffed;
                color: #52c41a;
            }
        }
    }
    .card-body {
        .info-row {
            display: flex;
            margin-bottom: 12rpx;
            font-size: 28rpx;
            .label {
                color: #999;
                width: 140rpx;
            }
            .value {
                color: #666;
                flex: 1;
            }
        }
    }
    .card-footer {
        margin-top: 20rpx;
        text-align: right;
        .btn-text {
            font-size: 26rpx;
            color: #007aff;
        }
    }
}
.empty {
    text-align: center;
    padding-top: 200rpx;
    color: #999;
    font-size: 28rpx;
}
</style>
