/**
 * Loyalty Config
 */

export const loyaltyTiers = [
    { id: 'bronze', name: 'Đồng', minPoints: 0, icon: '🥉' },
    { id: 'silver', name: 'Bạc', minPoints: 1000, icon: '🥈' },
    { id: 'gold', name: 'Vàng', minPoints: 5000, icon: '🥇' },
    { id: 'platinum', name: 'Bạch kim', minPoints: 15000, icon: '💎' },
    { id: 'diamond', name: 'Kim cương', minPoints: 50000, icon: '👑' }
]

export const pointsConfig = {
    pointsPerVnd: 1000, // 1 điểm cho mỗi 1000 VND
    pointValueInVnd: 100 // 1 điểm = 100 VND khi quy đổi
}
