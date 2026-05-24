#!/usr/bin/env python3
"""
Generate polished WordPress.org plugin directory assets.
Run from repository root: .venv-assets/bin/python scripts/generate-wordpress-org-assets.py
"""

from __future__ import annotations

import os
import math
from pathlib import Path
from textwrap import wrap

from PIL import Image, ImageDraw, ImageFilter, ImageFont

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "wordpress-org" / "assets"
GPT_SOURCE = ROOT / "wordpress-org" / "source" / "gpt-image-concept.png"

WP_BLUE = "#2271b1"
WP_BLUE_DARK = "#135e96"
WP_BLUE_LIGHT = "#72aee6"
WP_BG = "#f0f0f1"
WP_WHITE = "#ffffff"
WP_BORDER = "#c3c4c7"
WP_TEXT = "#1d2327"
WP_MUTED = "#646970"
WP_SIDEBAR = "#1d2327"
WP_SIDEBAR_ACTIVE = "#2271b1"
WP_WARNING_BG = "#fff8e5"
WP_WARNING_BORDER = "#dba617"
WP_SUCCESS = "#00a32a"
WP_DANGER = "#d63638"
SCREENSHOT_SIZE = (1280, 720)


def font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    candidates = [
        "/System/Library/Fonts/Supplemental/Arial Bold.ttf" if bold else "/System/Library/Fonts/Supplemental/Arial.ttf",
        "/System/Library/Fonts/SFNS.ttf",
        "/Library/Fonts/Arial Bold.ttf" if bold else "/Library/Fonts/Arial.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
    ]
    for path in candidates:
        if os.path.exists(path):
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def rgb(value: str) -> tuple[int, int, int]:
    value = value.lstrip("#")
    return tuple(int(value[i : i + 2], 16) for i in (0, 2, 4))


def rgba(value: str, alpha: int = 255) -> tuple[int, int, int, int]:
    return (*rgb(value), alpha)


def rounded(
    draw: ImageDraw.ImageDraw,
    box: tuple[int, int, int, int],
    radius: int,
    fill: str | tuple[int, int, int, int],
    outline: str | None = None,
    width: int = 1,
) -> None:
    draw.rounded_rectangle(box, radius=radius, fill=fill, outline=outline, width=width)


def shadow_card(
    img: Image.Image,
    box: tuple[int, int, int, int],
    radius: int,
    fill: str = WP_WHITE,
    outline: str = "#dcdcde",
    shadow_alpha: int = 28,
    offset: tuple[int, int] = (0, 8),
    blur: int = 18,
) -> None:
    shadow = Image.new("RGBA", img.size, (0, 0, 0, 0))
    shadow_draw = ImageDraw.Draw(shadow)
    shifted = (box[0] + offset[0], box[1] + offset[1], box[2] + offset[0], box[3] + offset[1])
    shadow_draw.rounded_rectangle(shifted, radius=radius, fill=(0, 0, 0, shadow_alpha))
    shadow = shadow.filter(ImageFilter.GaussianBlur(blur))
    img.alpha_composite(shadow)
    draw = ImageDraw.Draw(img)
    rounded(draw, box, radius, fill, outline, 1)


def text_size(draw: ImageDraw.ImageDraw, text: str, face: ImageFont.ImageFont) -> tuple[int, int]:
    box = draw.textbbox((0, 0), text, font=face)
    return box[2] - box[0], box[3] - box[1]


def centered_text(
    draw: ImageDraw.ImageDraw,
    box: tuple[int, int, int, int],
    text: str,
    face: ImageFont.ImageFont,
    fill: str,
) -> None:
    tw, th = text_size(draw, text, face)
    draw.text((box[0] + (box[2] - box[0] - tw) // 2, box[1] + (box[3] - box[1] - th) // 2 - 1), text, font=face, fill=fill)


def wrapped_text(
    draw: ImageDraw.ImageDraw,
    xy: tuple[int, int],
    text: str,
    face: ImageFont.ImageFont,
    fill: str,
    width: int,
    line_gap: int = 6,
    max_lines: int | None = None,
) -> int:
    words = text.split()
    lines: list[str] = []
    current = ""
    for word in words:
        candidate = word if not current else f"{current} {word}"
        if text_size(draw, candidate, face)[0] <= width or not current:
            current = candidate
        else:
            lines.append(current)
            current = word
    if current:
        lines.append(current)
    if max_lines:
        lines = lines[:max_lines]

    x, y = xy
    line_height = text_size(draw, "Ag", face)[1] + line_gap
    for line in lines:
        draw.text((x, y), line, font=face, fill=fill)
        y += line_height
    return y


def wp_button(
    draw: ImageDraw.ImageDraw,
    box: tuple[int, int, int, int],
    label: str,
    primary: bool = True,
    danger: bool = False,
) -> None:
    face = font(13)
    fill = WP_BLUE if primary else WP_WHITE
    outline = WP_BLUE_DARK if primary else "#8c8f94"
    text = WP_WHITE if primary else WP_TEXT
    if danger:
        fill = WP_DANGER
        outline = "#b32d2e"
        text = WP_WHITE
    rounded(draw, box, 4, fill, outline, 1)
    centered_text(draw, box, label, face, text)


def toggle(draw: ImageDraw.ImageDraw, x: int, y: int, on: bool) -> None:
    track = WP_SUCCESS if on else "#a7aaad"
    rounded(draw, (x, y, x + 58, y + 30), 15, track)
    knob_x = x + 30 if on else x + 4
    draw.ellipse((knob_x, y + 4, knob_x + 22, y + 26), fill=WP_WHITE)


def draw_brand_mark(draw: ImageDraw.ImageDraw, box: tuple[int, int, int, int], blue_bg: bool = True) -> None:
    x1, y1, x2, y2 = box
    w = x2 - x1
    h = y2 - y1
    cx = x1 + w / 2
    cy = y1 + h / 2
    radius = min(w, h) * 0.45
    points = []
    for i in range(8):
        angle = math.radians(22.5 + i * 45)
        points.append((cx + math.cos(angle) * radius, cy + math.sin(angle) * radius))
    draw.polygon(points, fill=WP_DANGER)
    draw.line(points + [points[0]], fill=WP_WHITE, width=max(3, w // 22), joint="curve")

    bx1 = x1 + int(w * 0.23)
    by1 = y1 + int(h * 0.29)
    bx2 = x1 + int(w * 0.77)
    by2 = y1 + int(h * 0.58)
    rounded(draw, (bx1, by1, bx2, by2), max(7, w // 17), WP_WHITE)
    draw.polygon(
        [
            (x1 + int(w * 0.40), y1 + int(h * 0.56)),
            (x1 + int(w * 0.31), y1 + int(h * 0.70)),
            (x1 + int(w * 0.51), y1 + int(h * 0.58)),
        ],
        fill=WP_WHITE,
    )

    lw = max(5, w // 14)
    draw.line(
        [(x1 + int(w * 0.28), y1 + int(h * 0.73)), (x1 + int(w * 0.74), y1 + int(h * 0.27))],
        fill=WP_BLUE_DARK,
        width=lw,
    )


def draw_feature_chip(draw: ImageDraw.ImageDraw, x: int, y: int, label: str, icon: str) -> int:
    face = font(14)
    tw, _ = text_size(draw, label, face)
    box = (x, y, x + tw + 52, y + 34)
    rounded(draw, box, 17, rgba("#ffffff", 235), "#dcdcde", 1)
    draw.ellipse((x + 10, y + 9, x + 25, y + 24), fill=WP_BLUE)
    if icon == "backup":
        draw.line((x + 14, y + 18, x + 18, y + 22, x + 24, y + 13), fill=WP_WHITE, width=2)
    elif icon == "trash":
        draw.rectangle((x + 14, y + 14, x + 23, y + 23), outline=WP_WHITE, width=2)
        draw.line((x + 13, y + 12, x + 24, y + 12), fill=WP_WHITE, width=2)
    else:
        draw.line((x + 14, y + 17, x + 23, y + 17), fill=WP_WHITE, width=2)
    draw.text((x + 34, y + 9), label, font=face, fill=WP_TEXT)
    return box[2] + 10


def create_banner(width: int, height: int) -> Image.Image:
    img = Image.new("RGBA", (width, height), rgba(WP_BG))
    draw = ImageDraw.Draw(img)

    # Soft editorial background, still rooted in the WordPress palette.
    for x in range(width):
        ratio = x / max(width - 1, 1)
        r = int(240 + (226 - 240) * ratio)
        g = int(240 + (241 - 240) * ratio)
        b = int(241 + (248 - 241) * ratio)
        draw.line((x, 0, x, height), fill=(r, g, b, 255))
    draw.polygon(
        [(0, height), (0, int(height * 0.74)), (int(width * 0.24), height)],
        fill=rgba(WP_BLUE_LIGHT, 28),
    )
    draw.polygon(
        [(width, 0), (width, int(height * 0.42)), (int(width * 0.78), 0)],
        fill=rgba(WP_BLUE, 24),
    )

    compact = width < 1000
    margin = 44 if compact else 88
    title_font = font(33 if width < 1000 else 58, True)
    sub_font = font(14 if width < 1000 else 24)
    chip_y = int(height * 0.70)

    draw_brand_mark(draw, (margin, int(height * 0.18), margin + int(height * 0.28), int(height * 0.46)), blue_bg=True)
    title_x = margin + int(height * 0.34)
    draw.text((title_x, int(height * 0.19)), "Delete & Disable", font=title_font, fill=WP_TEXT)
    draw.text((title_x, int(height * 0.37)), "Comments", font=title_font, fill=WP_TEXT)
    subtitle = "Comment cleanup for WordPress admins" if compact else "Comment cleanup and site-wide control for WordPress admins"
    draw.text((title_x, int(height * 0.56)), subtitle, font=sub_font, fill=WP_MUTED)

    chip_x = title_x
    if compact:
        chip_x = draw_feature_chip(draw, chip_x, chip_y, "Spam", "trash")
        chip_x = draw_feature_chip(draw, chip_x, chip_y, "Backup", "backup")
        draw_feature_chip(draw, chip_x, chip_y, "Disable", "disable")
        card = (width - 178, 42, width - 58, 162)
        shadow_card(img, card, 20, WP_WHITE, "#dcdcde", 24, (0, 8), 18)
        draw = ImageDraw.Draw(img)
        draw_brand_mark(draw, (card[0] + 22, card[1] + 22, card[2] - 22, card[3] - 22), blue_bg=True)
    else:
        chip_x = draw_feature_chip(draw, chip_x, chip_y, "Spam cleanup", "trash")
        chip_x = draw_feature_chip(draw, chip_x, chip_y, "CSV backup", "backup")
        draw_feature_chip(draw, chip_x, chip_y, "Disable toggle", "disable")

        preview_w = int(width * 0.32)
        preview_h = int(height * 0.68)
        px2 = width - margin
        px1 = px2 - preview_w
        py1 = int(height * 0.16)
        py2 = py1 + preview_h
        shadow_card(img, (px1, py1, px2, py2), 18, WP_WHITE, "#dcdcde", 32, (0, 10), 22)
        draw = ImageDraw.Draw(img)
        draw.text((px1 + 24, py1 + 22), "Tools", font=font(12, True), fill=WP_BLUE)
        draw.text((px1 + 24, py1 + 45), "Delete & Disable Comments", font=font(15, True), fill=WP_TEXT)
        mini_y = py1 + 86
        for label, value, color in [
            ("Spam comments", "Delete", WP_DANGER),
            ("All comments", "Backup + delete", WP_BLUE),
            ("Disable comments", "On", WP_SUCCESS),
        ]:
            rounded(draw, (px1 + 24, mini_y, px2 - 24, mini_y + 48), 8, "#f6f7f7", "#dcdcde")
            draw.text((px1 + 38, mini_y + 15), label, font=font(12, True), fill=WP_TEXT)
            draw.text((px2 - 170, mini_y + 15), value, font=font(12, True), fill=color)
            mini_y += 58

    return img.convert("RGB")


def create_icon(size: int) -> Image.Image:
    scale = 4
    canvas = Image.new("RGBA", (size * scale, size * scale), (0, 0, 0, 0))
    draw = ImageDraw.Draw(canvas)
    draw_brand_mark(draw, (0, 0, size * scale, size * scale), blue_bg=True)
    return canvas.resize((size, size), Image.Resampling.LANCZOS)


def create_gpt_brand_assets() -> bool:
    if not GPT_SOURCE.exists():
        return False

    src = Image.open(GPT_SOURCE).convert("RGB")
    w, h = src.size

    banner_crop = src.crop((0, int(h * 0.098), w, int(h * 0.908)))
    banner_crop.resize((1544, 500), Image.Resampling.LANCZOS).save(OUT / "banner-1544x500.png", optimize=True)
    banner_crop.resize((772, 250), Image.Resampling.LANCZOS).save(OUT / "banner-772x250.png", optimize=True)

    icon_crop = src.crop((int(w * 0.018), int(h * 0.208), int(w * 0.235), int(h * 0.750)))
    padded = Image.new("RGB", (icon_crop.width + 40, icon_crop.height + 40), WP_WHITE)
    padded.paste(icon_crop, (20, 20))
    padded.resize((256, 256), Image.Resampling.LANCZOS).save(OUT / "icon-256x256.png", optimize=True)
    padded.resize((128, 128), Image.Resampling.LANCZOS).save(OUT / "icon-128x128.png", optimize=True)
    return True


def admin_chrome(img: Image.Image, title: str) -> tuple[ImageDraw.ImageDraw, tuple[int, int, int, int]]:
    draw = ImageDraw.Draw(img)
    w, h = img.size
    sidebar = 215
    top = 32

    draw.rectangle((0, 0, w, top), fill=WP_SIDEBAR)
    draw.text((16, 8), "WordPress", font=font(12, True), fill=WP_WHITE)
    draw.text((w - 92, 8), "Howdy, admin", font=font(12), fill="#c3c4c7")
    draw.rectangle((0, top, sidebar, h), fill=WP_SIDEBAR)
    draw.rectangle((sidebar, top, w, h), fill=WP_BG)

    menu = [
        ("Dashboard", False),
        ("Posts", False),
        ("Media", False),
        ("Pages", False),
        ("Comments", False),
        ("Appearance", False),
        ("Plugins", False),
        ("Users", False),
        ("Tools", True),
        ("Delete & Disable Comments", True),
    ]
    y = 54
    for label, active in menu:
        if label == "Delete & Disable Comments":
            draw.rectangle((0, y - 7, sidebar, y + 23), fill=WP_SIDEBAR_ACTIVE)
            color = WP_WHITE
            x = 28
        else:
            color = WP_WHITE if active else "#c3c4c7"
            x = 18
        draw.text((x, y), label, font=font(12, active), fill=color)
        y += 31 if label != "Tools" else 35

    content = (sidebar + 38, 70, w - 42, h - 40)
    draw.text((content[0], 62), title, font=font(28, True), fill=WP_TEXT)
    return draw, content


def ui_card(
    img: Image.Image,
    box: tuple[int, int, int, int],
    title: str,
    body: str,
    buttons: list[tuple[str, bool, bool]] | None = None,
    toggle_on: bool | None = None,
    status: str | None = None,
    icon: str | None = None,
) -> None:
    shadow_card(img, box, 8, WP_WHITE, "#dcdcde", 20, (0, 4), 10)
    draw = ImageDraw.Draw(img)
    x1, y1, x2, y2 = box
    title_y = y1 + 20
    if icon:
        icon_fill = WP_DANGER if icon in ("trash", "download") else WP_BLUE
        rounded(draw, (x1 + 24, y1 + 22, x1 + 68, y1 + 66), 8, icon_fill)
        if icon == "trash":
            draw.rectangle((x1 + 38, y1 + 40, x1 + 54, y1 + 55), outline=WP_WHITE, width=2)
            draw.line((x1 + 36, y1 + 36, x1 + 56, y1 + 36), fill=WP_WHITE, width=2)
        elif icon == "download":
            draw.line((x1 + 46, y1 + 35, x1 + 46, y1 + 51), fill=WP_WHITE, width=3)
            draw.line((x1 + 39, y1 + 45, x1 + 46, y1 + 52, x1 + 53, y1 + 45), fill=WP_WHITE, width=3)
            draw.line((x1 + 37, y1 + 56, x1 + 55, y1 + 56), fill=WP_WHITE, width=3)
        else:
            draw.rounded_rectangle((x1 + 35, y1 + 37, x1 + 58, y1 + 51), radius=4, fill=WP_WHITE)
            draw.polygon([(x1 + 43, y1 + 50), (x1 + 38, y1 + 58), (x1 + 50, y1 + 51)], fill=WP_WHITE)
        title_y = y1 + 82
    draw.text((x1 + 24, title_y), title, font=font(18, True), fill=WP_TEXT)
    wrapped_text(draw, (x1 + 24, title_y + 35), body, font(14), WP_MUTED, x2 - x1 - 48, 5, 3)
    if buttons:
        widths = [max(128, text_size(draw, label, font(13))[0] + 32) for label, _, _ in buttons]
        inner_w = x2 - x1 - 48
        total_w = sum(widths) + 12 * (len(widths) - 1)
        if total_w <= inner_w:
            bx = x1 + 24
            by = y2 - 48
            for (label, primary, danger), bw in zip(buttons, widths):
                wp_button(draw, (bx, by, bx + bw, by + 32), label, primary, danger)
                bx += bw + 12
        else:
            by = y2 - 24 - (len(buttons) * 32 + (len(buttons) - 1) * 8)
            for (label, primary, danger), bw in zip(buttons, widths):
                wp_button(draw, (x1 + 24, by, x1 + 24 + min(bw, inner_w), by + 32), label, primary, danger)
                by += 40
    if toggle_on is not None:
        toggle(draw, x1 + 24, y2 - 47, toggle_on)
        draw.text((x1 + 96, y2 - 39), status or "", font=font(14), fill=WP_MUTED)


def screenshot_base() -> Image.Image:
    img = Image.new("RGBA", SCREENSHOT_SIZE, rgba(WP_BG))
    draw, content = admin_chrome(img, "Delete & Disable Comments")
    x1, y1, x2, _ = content
    gap = 18
    card_w = (x2 - x1 - gap * 2) // 3
    card_h = 270
    y = y1 + 40
    cards = [
        (
            x1,
            "Delete Spam Comments",
            "Remove all comments marked as spam from your database.",
            [("Delete Spam Comments", True, True)],
            None,
            None,
            "trash",
        ),
        (
            x1 + card_w + gap,
            "Delete All Comments",
            "Remove all comments from your website. You can download a backup before deletion.",
            [("Delete All Comments", True, True), ("Download Backup", False, False)],
            None,
            None,
            "download",
        ),
        (
            x1 + (card_w + gap) * 2,
            "Disable Comments",
            "Toggle comments on or off for your entire website.",
            [],
            False,
            "Comments are currently enabled",
            "comments",
        ),
    ]
    for card_x, title, body, buttons, toggle_on, status, icon in cards:
        ui_card(
            img,
            (card_x, y, card_x + card_w, y + card_h),
            title,
            body,
            buttons,
            toggle_on,
            status,
            icon,
        )
    return img

def screenshot_main() -> Image.Image:
    return screenshot_base().convert("RGB")


def modal(img: Image.Image, title: str, message: str, extra: str | None = None, backup_button: bool = False) -> Image.Image:
    base = img.convert("RGBA")
    overlay = Image.new("RGBA", base.size, (0, 0, 0, 96))
    base.alpha_composite(overlay)
    draw = ImageDraw.Draw(base)
    w, h = base.size
    mw = 520
    mh = 220 if backup_button else 180
    mx = (w - mw) // 2
    my = (h - mh) // 2
    shadow_card(base, (mx, my, mx + mw, my + mh), 10, WP_WHITE, "#dcdcde", 38, (0, 8), 18)
    draw = ImageDraw.Draw(base)
    draw.text((mx + 28, my + 24), title, font=font(18, True), fill=WP_TEXT)
    y = wrapped_text(draw, (mx + 28, my + 58), message, font(14), WP_TEXT, mw - 56, 6, 3)
    if extra:
        y += 8
        rounded(draw, (mx + 28, y, mx + mw - 28, y + 42), 6, WP_WARNING_BG, "#f0c33c")
        draw.text((mx + 42, y + 13), extra, font=font(12, True), fill=WP_TEXT)
    if backup_button:
        wp_button(draw, (mx + 28, my + mh - 55, mx + 178, my + mh - 22), "Download Backup", False, False)
    wp_button(draw, (mx + mw - 190, my + mh - 55, mx + mw - 112, my + mh - 22), "Yes", True, False)
    wp_button(draw, (mx + mw - 98, my + mh - 55, mx + mw - 28, my + mh - 22), "No", False, False)
    return base.convert("RGB")


def screenshot_spam_confirm() -> Image.Image:
    return modal(
        screenshot_base(),
        "Confirm spam cleanup",
        "Do you really want to delete all spam comments?",
    )


def screenshot_delete_all() -> Image.Image:
    return modal(
        screenshot_base(),
        "Delete all comments",
        "Do you really want to delete ALL comments? This action cannot be undone!",
        "Download a CSV backup before confirming.",
        True,
    )


def screenshot_disable_notice() -> Image.Image:
    img = Image.new("RGBA", SCREENSHOT_SIZE, rgba(WP_BG))
    draw, content = admin_chrome(img, "Delete & Disable Comments")
    x1, y1, x2, _ = content
    card_w = 820
    y = y1 + 40
    shadow_card(img, (x1, y, x1 + card_w, y + 390), 8, WP_WHITE, "#dcdcde", 20, (0, 4), 10)
    draw = ImageDraw.Draw(img)
    rounded(draw, (x1 + 24, y + 22, x1 + 68, y + 66), 8, WP_BLUE)
    draw.rounded_rectangle((x1 + 35, y + 37, x1 + 58, y + 51), radius=4, fill=WP_WHITE)
    draw.polygon([(x1 + 43, y + 50), (x1 + 38, y + 58), (x1 + 50, y + 51)], fill=WP_WHITE)
    draw.text((x1 + 24, y + 84), "Disable Comments", font=font(18, True), fill=WP_TEXT)
    draw.text((x1 + 24, y + 120), "Toggle comments on or off for your entire website.", font=font(14), fill=WP_MUTED)
    toggle(draw, x1 + 24, y + 156, True)
    draw.text((x1 + 96, y + 164), "Comments are currently disabled", font=font(14), fill=WP_MUTED)

    nx1, ny1 = x1 + 24, y + 218
    nx2, ny2 = x1 + card_w - 24, y + 350
    draw.rectangle((nx1, ny1, nx2, ny2), fill=WP_WARNING_BG, outline="#f0c33c")
    draw.rectangle((nx1, ny1, nx1 + 5, ny2), fill=WP_WARNING_BORDER)
    draw.text((nx1 + 22, ny1 + 18), "42 posts in your database still have open comments or pings.", font=font(14, True), fill=WP_TEXT)
    wrapped_text(
        draw,
        (nx1 + 22, ny1 + 46),
        "Click the button below to close them in a single safe SQL update. This bypasses save_post hooks and is compatible with WPML, Yoast, and other plugins.",
        font(12),
        WP_MUTED,
        nx2 - nx1 - 44,
        5,
        2,
    )
    wp_button(draw, (nx1 + 22, ny2 - 44, nx1 + 210, ny2 - 12), "Close all comments now", False, False)
    return img.convert("RGB")


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    if not create_gpt_brand_assets():
        create_banner(772, 250).save(OUT / "banner-772x250.png", optimize=True)
        create_banner(1544, 500).save(OUT / "banner-1544x500.png", optimize=True)
        create_icon(128).save(OUT / "icon-128x128.png", optimize=True)
        create_icon(256).save(OUT / "icon-256x256.png", optimize=True)
    screenshot_main().save(OUT / "screenshot-1.png", optimize=True)
    screenshot_spam_confirm().save(OUT / "screenshot-2.png", optimize=True)
    screenshot_delete_all().save(OUT / "screenshot-3.png", optimize=True)
    screenshot_disable_notice().save(OUT / "screenshot-4.png", optimize=True)
    print(f"Generated WordPress.org assets in {OUT}")


if __name__ == "__main__":
    main()
