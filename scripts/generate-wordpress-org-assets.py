#!/usr/bin/env python3
"""
Generate WordPress.org plugin directory assets (banner, icon, screenshots).
Run from repository root: .venv-assets/bin/python scripts/generate-wordpress-org-assets.py
"""

from __future__ import annotations

import os
from pathlib import Path

from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "wordpress-org" / "assets"

WP_BLUE = "#2271b1"
WP_BLUE_DARK = "#135e96"
WP_BLUE_LIGHT = "#72aee6"
WP_BG = "#f0f0f1"
WP_WHITE = "#ffffff"
WP_BORDER = "#c3c4c7"
WP_TEXT = "#1d2327"
WP_MUTED = "#646970"
WP_SIDEBAR = "#1d232d"
WP_SIDEBAR_ACTIVE = "#2271b1"
WP_WARNING_BG = "#fff8e5"
WP_WARNING_BORDER = "#dba617"
WP_SUCCESS = "#00a32a"


def load_font(size: int, bold: bool = False) -> ImageFont.FreeTypeFont | ImageFont.ImageFont:
    candidates = [
        "/System/Library/Fonts/SFNS.ttf",
        "/System/Library/Fonts/Supplemental/Arial Bold.ttf" if bold else "/System/Library/Fonts/Supplemental/Arial.ttf",
        "/Library/Fonts/Arial Bold.ttf" if bold else "/Library/Fonts/Arial.ttf",
        "/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf" if bold else "/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf",
    ]
    for path in candidates:
        if os.path.exists(path):
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def hex_color(value: str) -> tuple[int, int, int]:
    value = value.lstrip("#")
    return tuple(int(value[i : i + 2], 16) for i in (0, 2, 4))


def draw_rounded_rect(
    draw: ImageDraw.ImageDraw,
    xy: tuple[int, int, int, int],
    radius: int,
    fill: str,
    outline: str | None = None,
    width: int = 1,
) -> None:
    draw.rounded_rectangle(xy, radius=radius, fill=fill, outline=outline, width=width)


def draw_wp_button(
    draw: ImageDraw.ImageDraw,
    xy: tuple[int, int, int, int],
    label: str,
    font: ImageFont.FreeTypeFont | ImageFont.ImageFont,
    primary: bool = True,
) -> None:
    fill = WP_BLUE if primary else WP_WHITE
    text_color = WP_WHITE if primary else WP_TEXT
    outline = WP_BLUE_DARK if primary else WP_BORDER
    draw_rounded_rect(draw, xy, 3, fill, outline, 1)
    bbox = draw.textbbox((0, 0), label, font=font)
    tw, th = bbox[2] - bbox[0], bbox[3] - bbox[1]
    x = xy[0] + (xy[2] - xy[0] - tw) // 2
    y = xy[1] + (xy[3] - xy[1] - th) // 2 - 1
    draw.text((x, y), label, fill=text_color, font=font)


def draw_toggle(draw: ImageDraw.ImageDraw, x: int, y: int, enabled: bool) -> None:
    w, h = 46, 24
    track = WP_SUCCESS if enabled else "#a7aaad"
    draw_rounded_rect(draw, (x, y, x + w, y + h), h // 2, track)
    knob_x = x + w - h + 2 if enabled else x + 2
    draw.ellipse((knob_x, y + 2, knob_x + h - 4, y + h - 2), fill=WP_WHITE)


def draw_admin_chrome(
    img: Image.Image,
    page_title: str,
    menu_active: str = "Delete & Disable Comments",
) -> tuple[ImageDraw.ImageDraw, int, int, int, int]:
    """Draw WP admin sidebar + top bar; return draw and content area box."""
    draw = ImageDraw.Draw(img)
    w, h = img.size
    sidebar_w = int(w * 0.18)

    draw.rectangle((0, 0, w, 32), fill=WP_SIDEBAR)
    draw.text((12, 8), "WordPress", fill="#ffffff", font=load_font(13, bold=True))
    draw.rectangle((0, 32, sidebar_w, h), fill=WP_SIDEBAR)
    draw.rectangle((sidebar_w, 32, w, h), fill=WP_BG)

    menu_items = ["Dashboard", "Posts", "Media", "Pages", "Comments", "Appearance", "Plugins", "Users", "Tools", menu_active]
    y = 48
    for item in menu_items:
        active = item == menu_active
        if active:
            draw.rectangle((0, y - 4, sidebar_w, y + 22), fill=WP_SIDEBAR_ACTIVE)
        color = "#ffffff" if active else "#c3c4c7"
        prefix = "> " if active else "  "
        draw.text((12, y), prefix + item, fill=color, font=load_font(11))
        y += 28

    content_x1 = sidebar_w + 24
    content_y1 = 56
    content_x2 = w - 24
    content_y2 = h - 24
    draw.text((content_x1, content_y1), page_title, fill=WP_TEXT, font=load_font(22, bold=True))
    return draw, content_x1, content_y1 + 38, content_x2, content_y2


def draw_card(
    draw: ImageDraw.ImageDraw,
    box: tuple[int, int, int, int],
    title: str,
    body: str,
    buttons: list[tuple[str, bool]] | None = None,
    extra: str | None = None,
) -> None:
    draw_rounded_rect(draw, box, 4, WP_WHITE, WP_BORDER, 1)
    x1, y1, x2, y2 = box
    draw.text((x1 + 16, y1 + 14), title, fill=WP_TEXT, font=load_font(15, bold=True))
    draw.text((x1 + 16, y1 + 42), body, fill=WP_MUTED, font=load_font(11))
    if buttons:
        bx = x1 + 16
        by = y2 - 44
        for label, primary in buttons:
            bw = max(120, len(label) * 7 + 28)
            draw_wp_button(draw, (bx, by, bx + bw, by + 30), label, load_font(11), primary)
            bx += bw + 10
    if extra:
        draw.text((x1 + 16, y2 - 72), extra, fill=WP_MUTED, font=load_font(10))


def draw_modal(
    draw: ImageDraw.ImageDraw,
    img_size: tuple[int, int],
    message: str,
    show_backup_hint: bool = False,
) -> None:
    w, h = img_size
    overlay = Image.new("RGBA", img_size, (0, 0, 0, 90))
    img = draw._image if hasattr(draw, "_image") else None
    if img is not None:
        base = img.convert("RGBA")
        base = Image.alpha_composite(base, overlay)
        draw = ImageDraw.Draw(base)
        img_ref = base
    else:
        img_ref = None

    mw, mh = int(w * 0.42), 170 if show_backup_hint else 140
    mx = (w - mw) // 2
    my = (h - mh) // 2
    draw_rounded_rect(draw, (mx, my, mx + mw, my + mh), 6, WP_WHITE, WP_BORDER, 1)
    draw.text((mx + 20, my + 20), message, fill=WP_TEXT, font=load_font(12))
    if show_backup_hint:
        draw.text(
            (mx + 20, my + 58),
            "Tip: Download a CSV backup before confirming.",
            fill=WP_MUTED,
            font=load_font(10),
        )
        by = my + mh - 44
    else:
        by = my + mh - 44
    draw_wp_button(draw, (mx + mw - 170, by, mx + mw - 90, by + 30), "Yes", load_font(11), True)
    draw_wp_button(draw, (mx + mw - 80, by, mx + mw - 20, by + 30), "No", load_font(11), False)
    return draw, img_ref


def create_banner(width: int, height: int) -> Image.Image:
    img = Image.new("RGB", (width, height), hex_color(WP_BLUE))
    draw = ImageDraw.Draw(img)

    for i in range(width):
        ratio = i / max(width - 1, 1)
        r = int(34 + (19 - 34) * ratio)
        g = int(113 + (94 - 113) * ratio)
        b = int(177 + (150 - 177) * ratio)
        draw.line([(i, 0), (i, height)], fill=(r, g, b))

    title_size = 34 if width >= 1200 else 26
    subtitle_size = 16 if width >= 1200 else 13
    title_font = load_font(title_size, bold=True)
    sub_font = load_font(subtitle_size)

    draw.text((36, height // 2 - 36), "Delete & Disable Comments", fill=WP_WHITE, font=title_font)
    draw.text(
        (36, height // 2 + 8),
        "Clean spam, remove all comments safely, or disable comments site-wide",
        fill="#e8f0fb",
        font=sub_font,
    )

    bubble_x = width - 180
    bubble_y = height // 2 - 40
    draw.ellipse((bubble_x, bubble_y, bubble_x + 80, bubble_y + 60), fill=WP_BLUE_LIGHT, outline=WP_WHITE, width=2)
    draw.line([(bubble_x + 24, bubble_y + 30), (bubble_x + 56, bubble_y + 30)], fill=WP_WHITE, width=3)
    draw.line([(bubble_x + 40, bubble_y + 14), (bubble_x + 40, bubble_y + 46)], fill=WP_WHITE, width=3)

    return img


def create_icon(size: int) -> Image.Image:
    img = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    draw = ImageDraw.Draw(img)
    margin = int(size * 0.08)
    draw_rounded_rect(draw, (margin, margin, size - margin, size - margin), int(size * 0.18), WP_BLUE)

    cx, cy = size // 2, size // 2 - int(size * 0.04)
    r = int(size * 0.22)
    draw.ellipse((cx - r, cy - r, cx + r, cy + r), fill=WP_WHITE)
    lw = max(3, size // 32)
    draw.line([(cx - r // 2, cy), (cx + r // 2, cy)], fill=WP_BLUE, width=lw)
    draw.line([(cx, cy - r // 2), (cx, cy + r // 2)], fill=WP_BLUE, width=lw)

    tail = [
        (cx - int(size * 0.08), cy + r),
        (cx - int(size * 0.18), cy + r + int(size * 0.12)),
        (cx + int(size * 0.02), cy + r),
    ]
    draw.polygon(tail, fill=WP_WHITE)
    return img


def screenshot_main_panel() -> Image.Image:
    img = Image.new("RGB", (1280, 800), hex_color(WP_BG))
    draw, x1, y1, x2, y2 = draw_admin_chrome(img, "Delete & Disable Comments")

    card_h = 150
    gap = 16
    cards = [
        (
            "Delete Spam Comments",
            "Remove all comments marked as spam from your database.",
            [("Delete Spam Comments", True)],
        ),
        (
            "Delete All Comments",
            "Remove all comments from your website. You can download a backup before deletion.",
            [("Delete All Comments", True), ("Download Backup", False)],
        ),
        (
            "Disable Comments",
            "Toggle comments on or off for your entire website.",
            [],
        ),
    ]
    y = y1
    for title, body, buttons in cards:
        draw_card(draw, (x1, y, x2, y + card_h), title, body, buttons)
        if title == "Disable Comments":
            draw_toggle(draw, x1 + 16, y + card_h - 38, True)
            draw.text(
                (x1 + 72, y + card_h - 36),
                "Comments are currently disabled",
                fill=WP_MUTED,
                font=load_font(11),
            )
        y += card_h + gap

    return img


def screenshot_spam_confirm() -> Image.Image:
    img = screenshot_main_panel()
    overlay = Image.new("RGBA", img.size, (0, 0, 0, 90))
    base = img.convert("RGBA")
    base = Image.alpha_composite(base, overlay)
    draw = ImageDraw.Draw(base)
    w, h = base.size
    mw, mh = 440, 150
    mx, my = (w - mw) // 2, (h - mh) // 2
    draw_rounded_rect(draw, (mx, my, mx + mw, my + mh), 6, WP_WHITE, WP_BORDER, 1)
    draw.text(
        (mx + 20, my + 24),
        "Are you sure you want to delete all spam comments?",
        fill=WP_TEXT,
        font=load_font(12),
    )
    draw.text(
        (mx + 20, my + 48),
        "This action cannot be undone.",
        fill=WP_MUTED,
        font=load_font(10),
    )
    by = my + mh - 44
    draw_wp_button(draw, (mx + mw - 170, by, mx + mw - 90, by + 30), "Yes", load_font(11), True)
    draw_wp_button(draw, (mx + mw - 80, by, mx + mw - 20, by + 30), "No", load_font(11), False)
    return base.convert("RGB")


def screenshot_delete_all_backup() -> Image.Image:
    img = screenshot_main_panel()
    overlay = Image.new("RGBA", img.size, (0, 0, 0, 90))
    base = img.convert("RGBA")
    base = Image.alpha_composite(base, overlay)
    draw = ImageDraw.Draw(base)
    w, h = base.size
    mw, mh = 460, 190
    mx, my = (w - mw) // 2, (h - mh) // 2
    draw_rounded_rect(draw, (mx, my, mx + mw, my + mh), 6, WP_WHITE, WP_BORDER, 1)
    draw.text(
        (mx + 20, my + 20),
        "Delete ALL comments? This permanently removes every comment on your site.",
        fill=WP_TEXT,
        font=load_font(12),
    )
    draw.text(
        (mx + 20, my + 58),
        "Tip: Download a CSV backup before confirming.",
        fill=WP_MUTED,
        font=load_font(10),
    )
    draw_wp_button(draw, (mx + 20, my + 92, mx + 170, my + 122), "Download Backup", load_font(11), False)
    draw_wp_button(draw, (mx + mw - 170, my + mh - 44, mx + mw - 90, my + mh - 14), "Yes", load_font(11), True)
    draw_wp_button(draw, (mx + mw - 80, my + mh - 44, mx + mw - 20, my + mh - 14), "No", load_font(11), False)
    return base.convert("RGB")


def screenshot_toggle_maintenance() -> Image.Image:
    img = Image.new("RGB", (1280, 800), hex_color(WP_BG))
    draw, x1, y1, x2, y2 = draw_admin_chrome(img, "Delete & Disable Comments")

    card_top = y1
    card_h = 280
    draw_card(
        draw,
        (x1, card_top, x2, card_top + card_h),
        "Disable Comments",
        "Toggle comments on or off for your entire website.",
        [],
    )
    draw_toggle(draw, x1 + 16, card_top + card_h - 210, True)
    draw.text(
        (x1 + 72, card_top + card_h - 208),
        "Comments are currently disabled",
        fill=WP_MUTED,
        font=load_font(11),
    )

    notice_y1 = card_top + 90
    notice_y2 = notice_y1 + 120
    draw.rectangle((x1 + 16, notice_y1, x2 - 16, notice_y2), fill=hex_color(WP_WARNING_BG))
    draw.rectangle((x1 + 16, notice_y1, x1 + 20, notice_y2), fill=hex_color(WP_WARNING_BORDER))
    draw.text(
        (x1 + 32, notice_y1 + 12),
        "42 posts in your database still have open comments or pings.",
        fill=WP_TEXT,
        font=load_font(11, bold=True),
    )
    draw.text(
        (x1 + 32, notice_y1 + 36),
        "Click below to close them in a single safe SQL update (compatible with WPML & Yoast).",
        fill=WP_MUTED,
        font=load_font(10),
    )
    draw_wp_button(
        draw,
        (x1 + 32, notice_y2 - 46, x1 + 210, notice_y2 - 16),
        "Close all comments now",
        load_font(11),
        False,
    )

    return img


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)

    create_banner(772, 250).save(OUT / "banner-772x250.png", optimize=True)
    create_banner(1544, 500).save(OUT / "banner-1544x500.png", optimize=True)
    create_icon(128).save(OUT / "icon-128x128.png", optimize=True)
    create_icon(256).save(OUT / "icon-256x256.png", optimize=True)

    screenshot_main_panel().save(OUT / "screenshot-1.png", optimize=True)
    screenshot_spam_confirm().save(OUT / "screenshot-2.png", optimize=True)
    screenshot_delete_all_backup().save(OUT / "screenshot-3.png", optimize=True)
    screenshot_toggle_maintenance().save(OUT / "screenshot-4.png", optimize=True)

    print(f"Generated WordPress.org assets in {OUT}")


if __name__ == "__main__":
    main()
