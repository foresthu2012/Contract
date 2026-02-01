<template>
    <el-dialog v-model="showDialog" :title="formData.id ? '编辑合同' : '添加合同'" width="600px" destroy-on-close>
        <el-form :model="formData" label-width="100px" ref="formRef" :rules="formRules" class="page-form">
            <el-form-item label="合同标题" prop="title">
                <el-input v-model="formData.title" placeholder="请输入合同标题" class="input-width" />
            </el-form-item>
            <el-form-item label="会员" prop="member_id">
                <div class="flex items-center w-full">
                    <span v-if="selectedMemberName" class="mr-2">{{ selectedMemberName }} (ID: {{ formData.member_id }})</span>
                    <el-button type="primary" @click="showMemberSelect">选择会员</el-button>
                </div>
                <div class="form-tip">请选择要签署合同的会员</div>
            </el-form-item>
            <el-form-item label="关联订单" prop="order_id">
                 <div class="flex items-center w-full">
                     <span v-if="selectedOrderNo" class="mr-2">订单号: {{ selectedOrderNo }} (ID: {{ formData.order_id }})</span>
                     <el-button type="primary" @click="showOrderSelect">选择订单</el-button>
                     <el-button v-if="formData.order_id" link type="info" @click="clearOrder" class="ml-2">清除</el-button>
                 </div>
                 <div class="form-tip">可选：关联商城订单</div>
            </el-form-item>
            <el-form-item label="合同内容" prop="content">
                 <editor v-model="formData.content" placeholder="请输入合同内容" height="300px" />
                 <div class="form-tip">可选：填写合同正文内容（支持富文本）</div>
            </el-form-item>
            <el-form-item label="合同文件" prop="file_path">
                 <upload-file v-model="formData.file_path" :limit="1" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif" />
                 <div class="form-tip">请上传合同文件（支持PDF、Word及图片格式）</div>
            </el-form-item>
             <el-form-item label="已签署" v-if="formData.status == 1">
                 <el-tag type="success">是</el-tag>
            </el-form-item>
            <el-form-item label="签名图片" v-if="formData.status == 1 && formData.sign_image">
                 <el-image :src="formData.sign_image" style="width: 100px; height: 100px" fit="contain" />
            </el-form-item>
        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="showDialog = false">取消</el-button>
                <el-button type="primary" @click="confirm(formRef)" v-if="formData.status == 0">确定</el-button>
            </span>
        </template>
        <member-select-popup ref="memberSelectRef" @select="handleMemberSelect" />
        <order-select-popup ref="orderSelectRef" @select="handleOrderSelect" />
    </el-dialog>
</template>

<script lang="ts" setup>
import { ref, reactive } from 'vue'
import { addContract, editContract, getContractInfo } from '@/addon/Contract/api/contract'
import { ElMessage, FormInstance } from 'element-plus'
import MemberSelectPopup from './member_select_popup.vue'
import OrderSelectPopup from './order_select_popup.vue'

const showDialog = ref(false)
const loading = ref(false)
const formRef = ref<FormInstance>()
const memberSelectRef = ref()
const orderSelectRef = ref()
const selectedMemberName = ref('')
const selectedOrderNo = ref('')

const formData = reactive({
    id: 0,
    title: '',
    member_id: '',
    order_id: 0,
    file_path: '',
    content: '',
    status: 0,
    sign_image: ''
})

const formRules = reactive({
    title: [{ required: true, message: '请输入合同标题', trigger: 'blur' }],
    member_id: [{ required: true, message: '请选择会员', trigger: 'change' }],
    file_path: [
        { 
            validator: (rule: any, value: any, callback: any) => {
                // 如果是富文本内容（图片），认为是有效的合同内容
                if (formData.content) {
                    callback()
                    return
                }
                // 如果没有内容，也没有文件，则报错
                if (!value) {
                    callback(new Error('合同文件和合同内容至少填写一项'))
                } else {
                    callback()
                }
            }, 
            trigger: ['blur', 'change'] 
        }
    ]
})

const emit = defineEmits(['complete'])

const setFormData = async (row: any = null) => {
    loading.value = true
    Object.assign(formData, {
        id: 0,
        title: '',
        member_id: '',
        order_id: 0,
        file_path: '',
        content: '',
        status: 0,
        sign_image: ''
    })
    selectedMemberName.value = ''
    selectedOrderNo.value = ''
    
    if (row) {
        const res = await getContractInfo(row.id)
        if (res.data) {
            Object.assign(formData, res.data)
            
            // 回显会员名称
            if (res.data.member) {
                selectedMemberName.value = res.data.member.nickname || res.data.member.username || res.data.member.mobile
            }
            
            if (row.order_id) {
                selectedOrderNo.value = row.order_id 
            }
        }
    }
    showDialog.value = true
    loading.value = false
}

const showMemberSelect = () => {
    memberSelectRef.value.open()
}

const handleMemberSelect = (member: any) => {
    formData.member_id = member.member_id
    selectedMemberName.value = member.nickname || member.username || member.mobile
}

const showOrderSelect = () => {
    if (!formData.member_id) {
        ElMessage.warning('请先选择会员')
        return
    }
    orderSelectRef.value.open(formData.member_id)
}

const handleOrderSelect = (order: any) => {
    formData.order_id = order.order_id
    selectedOrderNo.value = order.order_no
}

const clearOrder = () => {
    formData.order_id = 0
    selectedOrderNo.value = ''
}

const confirm = async (formEl: FormInstance | undefined) => {
    if (!formEl) return
    await formEl.validate(async (valid) => {
        if (valid) {
            loading.value = true
            try {
                if (formData.id) {
                    await editContract(formData.id, formData)
                } else {
                    await addContract(formData)
                }
                ElMessage.success('操作成功')
                showDialog.value = false
                emit('complete')
            } catch (e) {
            }
            loading.value = false
        }
    })
}

defineExpose({
    setFormData
})
</script>

<style lang="scss" scoped></style>
