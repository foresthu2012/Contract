import request from '@/utils/request'

export function getContractList(params: Record<string, any>) {
  return request.get('Contract/contract', params)
}

export function getContractInfo(id: number) {
  return request.get(`Contract/contract/${id}`)
}

export function signContract(id: number, data: { sign_image: string }) {
  return request.post(`Contract/contract/sign/${id}`, data)
}
