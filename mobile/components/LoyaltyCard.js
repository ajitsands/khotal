import React, { useState } from 'react';
import { View, Text, StyleSheet, Dimensions, TouchableOpacity, Modal } from 'react-native';

const { width } = Dimensions.get('window');
const CARD_WIDTH = width - 40;
const CARD_HEIGHT = CARD_WIDTH * 0.58; // Standard card aspect ratio

export default function LoyaltyCard({ member }) {
    const [qrVisible, setQrVisible] = useState(false);

    if (!member) return null;

    const { first_name, last_name, card_type, membership_number, expiry_date } = member;
    const fullName = `${first_name} ${last_name}`.toUpperCase();

    // Custom themes based on card tier
    const getCardStyles = () => {
        switch (card_type) {
            case 'Gold':
                return {
                    bg: ['#1e1b18', '#382f25', '#8a6f44'],
                    colors: {
                        primaryText: '#ffffff',
                        secondaryText: '#e5c185',
                        tierText: '#f3e5ab',
                        accentLine: '#fbbf24'
                    },
                    name: 'THE K PLUS GOLD'
                };
            case 'Brown':
                return {
                    bg: ['#231912', '#412d1f', '#604430'],
                    colors: {
                        primaryText: '#ffffff',
                        secondaryText: '#cca080',
                        tierText: '#dfb79b',
                        accentLine: '#92613d'
                    },
                    name: 'THE K PLUS BROWN'
                };
            case 'Booker':
                return {
                    bg: ['#0f172a', '#1e293b', '#334155'],
                    colors: {
                        primaryText: '#ffffff',
                        secondaryText: '#94a3b8',
                        tierText: '#cbd5e1',
                        accentLine: '#64748b'
                    },
                    name: 'THE K REWARD BOOKER'
                };
            case 'Silver':
            default:
                return {
                    bg: ['#2d3748', '#4a5568', '#718096'],
                    colors: {
                        primaryText: '#ffffff',
                        secondaryText: '#cbd5e0',
                        tierText: '#e2e8f0',
                        accentLine: '#a0aec0'
                    },
                    name: 'THE K PLUS SILVER'
                };
        }
    };

    const cardTheme = getCardStyles();

    return (
        <View style={styles.container}>
            <TouchableOpacity 
                activeOpacity={0.9} 
                onPress={() => setQrVisible(true)}
                style={[
                    styles.card, 
                    { backgroundColor: cardTheme.bg[0] }
                ]}
            >
                {/* Metallic gradients simulated via layered views */}
                <View style={[styles.gradientLayer, { backgroundColor: cardTheme.bg[1], opacity: 0.7 }]} />
                <View style={[styles.accentCorner, { borderBottomColor: cardTheme.colors.accentLine }]} />

                {/* Brand Header */}
                <View style={styles.cardHeader}>
                    <View>
                        <Text style={[styles.hotelName, { color: cardTheme.colors.primaryText }]}>THE K HOTEL</Text>
                        <Text style={[styles.hotelSub, { color: cardTheme.colors.secondaryText }]}>BAHRAIN</Text>
                    </View>
                    <View style={styles.chip} />
                </View>

                {/* Card Number / Membership Tier */}
                <View style={styles.cardMiddle}>
                    <Text style={[styles.cardNumber, { color: cardTheme.colors.primaryText }]}>
                        {membership_number && membership_number.replace(/[\s-]/g, '').length === 16 ? membership_number.replace(/[\s-]/g, '').replace(/(.{4})/g, '$1 ').trim() : membership_number}
                    </Text>
                </View>

                {/* Card Footer */}
                <View style={styles.cardFooter}>
                    <View>
                        <Text style={[styles.label, { color: cardTheme.colors.secondaryText }]}>CARDHOLDER</Text>
                        <Text style={[styles.cardholderName, { color: cardTheme.colors.primaryText }]} numberOfLines={1}>
                            {fullName}
                        </Text>
                    </View>
                    <View style={styles.footerRight}>
                        <View style={{ marginRight: 15 }}>
                            <Text style={[styles.label, { color: cardTheme.colors.secondaryText }]}>EXPIRES</Text>
                            <Text style={[styles.expiryText, { color: cardTheme.colors.primaryText }]}>
                                {expiry_date ? expiry_date.substring(5, 7) + '/' + expiry_date.substring(2, 4) : '12/27'}
                            </Text>
                        </View>
                        <View>
                            <Text style={[styles.tierLabel, { color: cardTheme.colors.tierText }]}>
                                {cardTheme.name}
                            </Text>
                        </View>
                    </View>
                </View>
            </TouchableOpacity>

            <Text style={styles.tapTip}>Tap card to display Digital QR Code</Text>

            {/* QR Code Modal Overlay */}
            <Modal
                animationType="fade"
                transparent={true}
                visible={qrVisible}
                onRequestClose={() => setQrVisible(false)}
            >
                <TouchableOpacity 
                    style={styles.modalOverlay} 
                    activeOpacity={1} 
                    onPress={() => setQrVisible(false)}
                >
                    <View style={styles.modalContent}>
                        <Text style={styles.modalHotelTitle}>THE K HOTEL</Text>
                        <Text style={styles.modalMemberName}>{fullName}</Text>
                        <Text style={styles.modalCardNum}>{membership_number && membership_number.replace(/[\s-]/g, '').length === 16 ? membership_number.replace(/[\s-]/g, '').replace(/(.{4})/g, '$1 ').trim() : membership_number}</Text>
                        
                        {/* Mock QR Code graphic */}
                        <View style={styles.qrContainer}>
                            <View style={styles.mockQrCode}>
                                {/* Grid patterns simulating QR Code details */}
                                <View style={styles.qrCornerBlock} />
                                <View style={[styles.qrCornerBlock, { right: 15 }]} />
                                <View style={[styles.qrCornerBlock, { bottom: 15 }]} />
                                <View style={styles.qrCenterDot} />
                                <Text style={styles.scanCodeText}>MEMBER PASS</Text>
                            </View>
                        </View>

                        <Text style={styles.scanNotice}>Scan at Front Office or any F&B outlet to claim your discounts and rewards.</Text>
                        
                        <TouchableOpacity style={styles.closeBtn} onPress={() => setQrVisible(false)}>
                            <Text style={styles.closeBtnText}>Close</Text>
                        </TouchableOpacity>
                    </View>
                </TouchableOpacity>
            </Modal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        alignItems: 'center',
        marginVertical: 20,
    },
    card: {
        width: CARD_WIDTH,
        height: CARD_HEIGHT,
        borderRadius: 16,
        padding: 24,
        justifyContent: 'space-between',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 10 },
        shadowOpacity: 0.4,
        shadowRadius: 12,
        elevation: 10,
        position: 'overflow',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.08)',
    },
    gradientLayer: {
        ...StyleSheet.absoluteFillObject,
        borderRadius: 16,
    },
    accentCorner: {
        position: 'absolute',
        bottom: 0,
        right: 0,
        width: 0,
        height: 0,
        borderStyle: 'solid',
        borderRightWidth: CARD_WIDTH * 0.4,
        borderBottomWidth: CARD_HEIGHT * 0.4,
        borderRightColor: 'transparent',
        opacity: 0.15,
    },
    cardHeader: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'center',
    },
    hotelName: {
        fontSize: 20,
        fontWeight: '800',
        letterSpacing: 2,
    },
    hotelSub: {
        fontSize: 10,
        fontWeight: '600',
        letterSpacing: 1.5,
        marginTop: 2,
    },
    chip: {
        width: 42,
        height: 32,
        borderRadius: 6,
        backgroundColor: '#ecc94b',
        opacity: 0.8,
        borderWidth: 1,
        borderColor: '#d69e2e',
    },
    cardMiddle: {
        marginVertical: 10,
    },
    cardNumber: {
        fontSize: 22,
        fontWeight: '700',
        letterSpacing: 3,
        fontFamily: 'monospace',
    },
    cardFooter: {
        flexDirection: 'row',
        justifyContent: 'space-between',
        alignItems: 'flex-end',
    },
    label: {
        fontSize: 8,
        fontWeight: '600',
        letterSpacing: 1,
        marginBottom: 4,
    },
    cardholderName: {
        fontSize: 14,
        fontWeight: '700',
        letterSpacing: 1,
        maxWidth: CARD_WIDTH * 0.45,
    },
    footerRight: {
        flexDirection: 'row',
        alignItems: 'flex-end',
    },
    expiryText: {
        fontSize: 13,
        fontWeight: '700',
    },
    tierLabel: {
        fontSize: 11,
        fontWeight: '800',
        letterSpacing: 1,
    },
    tapTip: {
        color: '#94a3b8',
        fontSize: 12,
        marginTop: 10,
        fontStyle: 'italic',
    },
    modalOverlay: {
        flex: 1,
        backgroundColor: 'rgba(0,0,0,0.85)',
        justifyContent: 'center',
        alignItems: 'center',
        padding: 20,
    },
    modalContent: {
        backgroundColor: '#1e293b',
        borderRadius: 24,
        padding: 30,
        width: '100%',
        maxWidth: 340,
        alignItems: 'center',
        borderWidth: 1,
        borderColor: 'rgba(255,255,255,0.08)',
    },
    modalHotelTitle: {
        color: '#fbbf24',
        fontSize: 22,
        fontWeight: '800',
        letterSpacing: 2,
    },
    modalMemberName: {
        color: '#fff',
        fontSize: 16,
        fontWeight: '700',
        marginTop: 10,
    },
    modalCardNum: {
        color: '#94a3b8',
        fontSize: 14,
        fontFamily: 'monospace',
        marginTop: 4,
    },
    qrContainer: {
        backgroundColor: '#fff',
        padding: 20,
        borderRadius: 16,
        marginVertical: 25,
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.3,
        shadowRadius: 8,
        elevation: 5,
    },
    mockQrCode: {
        width: 180,
        height: 180,
        backgroundColor: '#fff',
        position: 'relative',
        justifyContent: 'center',
        alignItems: 'center',
        borderWidth: 1,
        borderColor: '#e2e8f0',
    },
    qrCornerBlock: {
        position: 'absolute',
        width: 45,
        height: 45,
        borderWidth: 12,
        borderColor: '#0f172a',
        top: 15,
        left: 15,
    },
    qrCenterDot: {
        width: 30,
        height: 30,
        backgroundColor: '#0f172a',
    },
    scanCodeText: {
        position: 'absolute',
        bottom: 5,
        fontSize: 10,
        fontWeight: '700',
        color: '#0f172a',
    },
    scanNotice: {
        color: '#94a3b8',
        fontSize: 12,
        textAlign: 'center',
        lineHeight: 18,
        marginHorizontal: 10,
    },
    closeBtn: {
        marginTop: 25,
        backgroundColor: '#d97706',
        paddingVertical: 12,
        paddingHorizontal: 40,
        borderRadius: 25,
        width: '100%',
        alignItems: 'center',
    },
    closeBtnText: {
        color: '#fff',
        fontWeight: '700',
        fontSize: 15,
    }
});
