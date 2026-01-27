import { BaseApiService } from './BaseApiService';
import type { ApiResponse } from '../types';

export interface EmailCampaign {
    id: number;
    name: string;
    subject: string;
    content_html: string;
    status: 'draft' | 'scheduled' | 'sending' | 'sent' | 'failed';
    recipients?: string[];
    scheduled_at?: string;
    sent_at?: string;
    stats?: {
        opens: number;
        clicks: number;
    };
    user?: {
        name: string;
    };
    created_at: string;
}

export interface EmailTemplate {
    id: number;
    name: string;
    code: string;
    subject: string;
    body: string;
    type: string;
    variables?: string[];
    is_active: boolean;
}

export class EmailService extends BaseApiService<EmailCampaign> {
    protected readonly endpoint = '/admin/email';

    async getCampaigns(params?: any) {
        return this.getAll(params);
    }

    async getCampaign(id: number) {
        return this.getById(id);
    }

    async createCampaign(data: Partial<EmailCampaign>) {
        return this.create(data as any);
    }

    async updateCampaign(id: number, data: Partial<EmailCampaign>) {
        return this.update(id, data as any);
    }

    async deleteCampaign(id: number) {
        return this.delete(id);
    }

    async sendCampaign(id: number) {
        const response = await this.httpClient.post<ApiResponse<any>>(`${this.formattedEndpoint}/campaigns/${id}/send`);
        return response.data;
    }

    async getTemplates() {
        const response = await this.httpClient.get<ApiResponse<EmailTemplate[]>>(`${this.formattedEndpoint}/templates`);
        return response.data.data;
    }
}

export const emailService = new EmailService();
export default emailService;
